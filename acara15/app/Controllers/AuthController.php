<?php

namespace App\Controllers;

use Throwable;

class AuthController
{
    public function loginForm(): void
    {
        if (!empty($_SESSION['logged_in'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $title = 'Login | SI Akademik';
        $content = __DIR__ . '/../Views/auth/login.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function login(): void
    {
        try {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($username === 'admin' && $password === 'admin123') {
                session_regenerate_id(true);
                $_SESSION['user_id'] = 1;
                $_SESSION['user_name'] = 'Admin';
                $_SESSION['logged_in'] = true;
                $_SESSION['flash'] = 'Selamat datang, Admin';

                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            }

            http_response_code(422);
            $error = 'Username atau password salah.';
            $title = 'Login | SI Akademik';
            $content = __DIR__ . '/../Views/auth/login.php';
            require __DIR__ . '/../Views/layouts/main.php';
        } catch (Throwable $exception) {
            $this->logException($exception);
            http_response_code(500);
            $error = 'Terjadi kesalahan. Silakan coba kembali.';
            $title = 'Login | SI Akademik';
            $content = __DIR__ . '/../Views/auth/login.php';
            require __DIR__ . '/../Views/layouts/main.php';
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        $_SESSION['flash'] = 'Anda telah logout';

        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    private function logException(Throwable $exception): void
    {
        $logDirectory = __DIR__ . '/../../storage/logs';
        if (
            !is_dir($logDirectory)
            && !mkdir($logDirectory, 0775, true)
            && !is_dir($logDirectory)
        ) {
            error_log('Unable to create application log directory.');
            return;
        }

        $entry = sprintf(
            "%s [Auth login] %s: %s in %s:%d%s%s%s",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            PHP_EOL,
            $exception->getTraceAsString(),
            PHP_EOL
        );
        if (error_log($entry, 3, $logDirectory . '/app.log') !== true) {
            error_log('Unable to write application log file.');
        }
    }
}