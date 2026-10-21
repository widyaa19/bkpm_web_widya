<?php

namespace App\Core;

use App\Models\MahasiswaRepository;
use App\Models\ProdiRepository;
use App\Services\MahasiswaService;

class Router
{
    public function dispatch(array $routes): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $uri = rtrim($uri, '/') ?: '/';
        $method = $_SERVER['REQUEST_METHOD'];
        $matchedRoute = null;
        $parameters = [];

        foreach ($routes[$method] ?? [] as $route => $definition) {
            $pattern = $this->routePattern($route);
            if ($route === $uri) {
                $matchedRoute = $definition;
                break;
            }

            if (preg_match('#^' . $pattern . '$#', $uri, $matches) === 1) {
                $matchedRoute = $definition;
                foreach ($matches as $name => $value) {
                    if (is_string($name)) {
                        $parameters[] = (int) $value;
                    }
                }
                break;
            }
        }

        if ($matchedRoute !== null) {
            [$controllerName, $action, $middleware] = array_pad($matchedRoute, 3, []);

            foreach ($middleware as $middlewareClass) {
                (new $middlewareClass())->handle();
            }

            $controllerClass = "App\\Controllers\\{$controllerName}";
            $controller = $this->makeController($controllerClass);
            $controller->$action(...$parameters);
            return;
        }

        $this->respondNotFoundOrMethodNotAllowed($routes, $uri);
    }

    private function makeController(string $controllerClass): object
    {
        if (
            $controllerClass !== \App\Controllers\MahasiswaController::class
            && $controllerClass !== \App\Controllers\ApiMahasiswaController::class
        ) {
            return new $controllerClass();
        }

        $database = Database::getInstance();
        $mahasiswaRepository = new MahasiswaRepository($database);
        $prodiRepository = new ProdiRepository($database);
        $service = new MahasiswaService($mahasiswaRepository, $prodiRepository);

        if ($controllerClass === \App\Controllers\ApiMahasiswaController::class) {
            return new $controllerClass($mahasiswaRepository, $service);
        }

        return new $controllerClass($mahasiswaRepository, $prodiRepository, $service);
    }

    private function respondNotFoundOrMethodNotAllowed(array $routes, string $uri): void
    {
        if (str_starts_with($uri, '/api/')) {
            $allowedMethods = [];
            foreach ($routes as $routeMethod => $definitions) {
                foreach ($definitions as $route => $_definition) {
                    if (
                        $route === $uri
                        || preg_match('#^' . $this->routePattern($route) . '$#', $uri) === 1
                    ) {
                        $allowedMethods[] = $routeMethod;
                        break;
                    }
                }
            }

            header('Content-Type: application/json; charset=utf-8');
            if ($allowedMethods !== []) {
                sort($allowedMethods);
                header('Allow: ' . implode(', ', $allowedMethods));
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Method tidak diizinkan.',
                    'data' => null,
                ]);
                return;
            }

            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint tidak ditemukan.',
                'data' => null,
            ]);
            return;
        }

        http_response_code(404);
        echo '404 - Halaman tidak ditemukan';
    }

    private function routePattern(string $route): string
    {
        return preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn (array $matches): string => '(?P<' . $matches[1] . '>[0-9]+)',
            $route
        );
    }
}
