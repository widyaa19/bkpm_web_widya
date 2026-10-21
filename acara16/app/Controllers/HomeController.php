<?php

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        if (empty($_SESSION['logged_in'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $title = 'Dashboard | SI Akademik';
        $content = __DIR__ . '/../Views/dashboard.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }
}