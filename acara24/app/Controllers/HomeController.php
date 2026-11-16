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
        $bodyClass = 'dashboard-page';
        $pageCss = BASE_URL . '/views/dashboard.css';
        $pageScript = BASE_URL . '/views/app.js';
        $content = __DIR__ . '/../../public/views/dashboard.php';
        require __DIR__ . '/../../public/views/layouts/main.php';
    }
}