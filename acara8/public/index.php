<?php

session_start();

define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once __DIR__ . '/../app/Models/Mahasiswa.php';
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/MahasiswaModel.php';
require_once __DIR__ . '/../app/Models/ProdiModel.php';
require_once __DIR__ . '/../app/Models/MatakuliahModel.php';
require_once __DIR__ . '/../app/Core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
require_once __DIR__ . '/../app/Controllers/ProdiController.php';
require_once __DIR__ . '/../app/Controllers/MatakuliahController.php';
require_once __DIR__ . '/../routes/web.php';

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
    $pattern = preg_replace_callback(
        '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
        static fn (array $matches): string => '(?P<' . $matches[1] . '>[0-9]+)',
        $route
    );

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
    $controller = new $controllerClass();
    $controller->$action(...$parameters);
    exit;
}

http_response_code(404);
echo '404 - Halaman tidak ditemukan';
