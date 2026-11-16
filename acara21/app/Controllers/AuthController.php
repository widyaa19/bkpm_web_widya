<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\User;
use InvalidArgumentException;
use JsonException;
use PDOException;
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
        $content = __DIR__ . '/../../public/views/auth/login.php';
        require __DIR__ . '/../../public/views/layouts/main.php';
    }

    public function registerForm(): void
    {
        if (!empty($_SESSION['logged_in'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $title = 'Registrasi | SI Akademik';
        $content = __DIR__ . '/../../public/views/auth/register.php';
        require __DIR__ . '/../../public/views/layouts/main.php';
    }

    public function login(): void
    {
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($contentType === 'application/json') {
            $this->loginApi();
            return;
        }

        try {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if (!is_string($username) || !is_string($password)) {
                $username = '';
                $password = '';
            }
            $username = trim($username);
            $user = (new User())->findByUsername($username);

            if ($user !== null && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_role'] = 'user';
                $_SESSION['logged_in'] = true;
                $_SESSION['flash'] = 'Selamat datang, ' . $user['username'];

                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            }

            http_response_code(422);
            $error = 'Username atau password salah.';
            $title = 'Login | SI Akademik';
            $content = __DIR__ . '/../../public/views/auth/login.php';
            require __DIR__ . '/../../public/views/layouts/main.php';
        } catch (Throwable $exception) {
            $this->logException($exception);
            http_response_code(500);
            $error = 'Terjadi kesalahan. Silakan coba kembali.';
            $title = 'Login | SI Akademik';
            $content = __DIR__ . '/../../public/views/auth/login.php';
            require __DIR__ . '/../../public/views/layouts/main.php';
        }
    }

    private function loginApi(): void
    {
        $body = file_get_contents('php://input');
        if ($body === false) {
            Response::json(400, false, 'Body request tidak dapat dibaca.');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            Response::json(400, false, 'Format JSON tidak valid.');
        }

        if (
            !is_array($data)
            || !isset($data['username'], $data['password'])
            || !is_string($data['username'])
            || !is_string($data['password'])
            || trim($data['username']) === ''
            || $data['password'] === ''
        ) {
            Response::json(400, false, 'Username dan password wajib diisi.');
        }

        try {
            $user = (new User())->findByUsername(trim($data['username']));
            if ($user === null || !password_verify($data['password'], $user['password'])) {
                Response::json(401, false, 'Username atau password salah.');
            }

            $token = Auth::generateToken([
                'id' => (int) $user['id'],
                'role' => $user['role'],
            ]);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API login');
            Response::json(500, false, 'Login gagal diproses.');
        }

        Response::json(200, true, 'Login berhasil.', [
            'token' => $token,
            'role' => $user['role'],
        ]);
    }

    public function register(): void
    {
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($contentType === 'application/x-www-form-urlencoded' || $contentType === 'multipart/form-data') {
            $this->registerFromForm();
            return;
        }

        if ($contentType !== 'application/json') {
            Response::json(415, false, 'Content-Type harus application/json.');
        }

        $body = file_get_contents('php://input');
        if ($body === false) {
            Response::json(400, false, 'Body request tidak dapat dibaca.');
        }

        try {
            $input = json_decode(
                $body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            Response::json(400, false, 'Format JSON tidak valid.');
        }

        if (!is_array($input)) {
            Response::json(400, false, 'Body request harus berupa object JSON.');
        }

        foreach (['username', 'email', 'password'] as $field) {
            if (!isset($input[$field]) || !is_string($input[$field]) || trim($input[$field]) === '') {
                Response::json(400, false, 'Username, email, dan password wajib diisi.');
            }
        }

        try {
            $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
            $id = (new User())->create($input);
        } catch (InvalidArgumentException $exception) {
            Response::json(422, false, $exception->getMessage());
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                Response::json(409, false, 'Username atau email sudah terdaftar.');
            }

            $this->logException($exception, 'API register');
            Response::json(500, false, 'Registrasi gagal diproses.');
        } catch (Throwable $exception) {
            $this->logException($exception, 'API register');
            Response::json(500, false, 'Registrasi gagal diproses.');
        }

        Response::json(201, true, 'Registrasi berhasil.', ['id' => $id]);
    }

    private function registerFromForm(): void
    {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $formData = [
            'username' => is_string($username) ? trim($username) : '',
            'email' => is_string($email) ? trim($email) : '',
        ];

        if (!is_string($username) || !is_string($email) || !is_string($password) || !is_string($passwordConfirmation)) {
            $error = 'Data registrasi tidak valid.';
            $this->renderRegistrationForm($error, $formData, 400);
            return;
        }

        if ($password === '' || $passwordConfirmation === '' || $formData['username'] === '' || $formData['email'] === '') {
            $error = 'Username, email, dan password wajib diisi.';
            $this->renderRegistrationForm($error, $formData, 422);
            return;
        }

        if ($password !== $passwordConfirmation) {
            $error = 'Konfirmasi password tidak sama.';
            $this->renderRegistrationForm($error, $formData, 422);
            return;
        }

        try {
            (new User())->create([
                'username' => $formData['username'],
                'email' => $formData['email'],
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->renderRegistrationForm($exception->getMessage(), $formData, 422);
            return;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->renderRegistrationForm('Username atau email sudah terdaftar.', $formData, 409);
                return;
            }

            $this->logException($exception, 'Web register');
            $this->renderRegistrationForm('Registrasi gagal diproses. Silakan coba kembali.', $formData, 500);
            return;
        } catch (Throwable $exception) {
            $this->logException($exception, 'Web register');
            $this->renderRegistrationForm('Registrasi gagal diproses. Silakan coba kembali.', $formData, 500);
            return;
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Registrasi berhasil. Silakan login.',
        ];
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    private function renderRegistrationForm(string $error, array $formData, int $statusCode): void
    {
        http_response_code($statusCode);
        $title = 'Registrasi | SI Akademik';
        $content = __DIR__ . '/../../public/views/auth/register.php';
        require __DIR__ . '/../../public/views/layouts/main.php';
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

    private function logException(Throwable $exception, string $context = 'Auth login'): void
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
            "%s [%s] %s: %s in %s:%d%s%s%s",
            date('Y-m-d H:i:s'),
            $context,
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