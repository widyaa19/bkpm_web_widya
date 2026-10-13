<?php

use App\Controllers\ApiMahasiswaController;
use App\Core\Database;
use App\Models\MahasiswaRepository;
use App\Models\ProdiRepository;
use App\Services\MahasiswaService;

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/Models/Mahasiswa.php';
require_once __DIR__ . '/../../app/Models/BaseModel.php';
require_once __DIR__ . '/../../app/Models/MahasiswaRepository.php';
require_once __DIR__ . '/../../app/Models/ProdiRepository.php';
require_once __DIR__ . '/../../app/Core/BaseController.php';
require_once __DIR__ . '/../../app/Services/MahasiswaService.php';
require_once __DIR__ . '/../../app/Controllers/ApiMahasiswaController.php';

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan.',
        'data' => null,
    ]);
    exit;
}

try {
    $database = Database::getInstance();
    $repository = new MahasiswaRepository($database);
    $prodiRepository = new ProdiRepository($database);
    $service = new MahasiswaService($repository, $prodiRepository);
    $controller = new ApiMahasiswaController($repository, $service);

    if ($method === 'GET') {
        $controller->index();
        exit;
    }

    $controller->store();
} catch (Throwable $exception) {
    error_log(sprintf(
        '%s [API mahasiswa bootstrap] %s: %s',
        date('Y-m-d H:i:s'),
        get_class($exception),
        $exception->getMessage()
    ));

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.',
        'data' => null,
    ]);
}
