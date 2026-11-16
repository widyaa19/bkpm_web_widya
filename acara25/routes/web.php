<?php

return [
    'GET' => [
        '/api/buku' => ['BukuController', 'index'],
        '/api/buku/{id}' => ['BukuController', 'show'],
        '/api/mahasiswa' => ['ApiMahasiswaController', 'index'],
        '/api/mahasiswa/{id}' => ['ApiMahasiswaController', 'show'],
        '/' => ['HomeController', 'index', [App\Core\Middleware\AuthMiddleware::class]],
        '/dashboard' => ['HomeController', 'index', [App\Core\Middleware\AuthMiddleware::class]],
        '/login' => ['AuthController', 'loginForm'],
        '/register' => ['AuthController', 'registerForm'],
        '/logout' => ['AuthController', 'logout'],
    ],
    'POST' => [
        '/api/buku' => ['BukuController', 'store'],
        '/api/mahasiswa' => ['ApiMahasiswaController', 'store'],
        '/register' => ['AuthController', 'register'],
        '/login' => ['AuthController', 'login'],
    ],
    'PUT' => [
        '/api/buku' => ['BukuController', 'update'],
        '/api/buku/{id}' => ['BukuController', 'update'],
        '/api/mahasiswa' => ['ApiMahasiswaController', 'update'],
        '/api/mahasiswa/{id}' => ['ApiMahasiswaController', 'update'],
    ],
    'DELETE' => [
        '/api/buku' => ['BukuController', 'destroy'],
        '/api/buku/{id}' => ['BukuController', 'destroy'],
        '/api/mahasiswa' => ['ApiMahasiswaController', 'destroy'],
        '/api/mahasiswa/{id}' => ['ApiMahasiswaController', 'destroy'],
    ],
];