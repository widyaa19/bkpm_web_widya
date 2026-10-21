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
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
if ($pathInfo === '' && preg_match('#/api/mahasiswa\.php(/.*)?$#', $_SERVER['REQUEST_URI'] ?? '', $matches) === 1) {
    $pathInfo = $matches[1] ?? '';
}

$id = null;
if ($pathInfo !== '') {
    if (preg_match('#^/([0-9]+)$#', $pathInfo, $matches) !== 1) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint tidak ditemukan.',
            'data' => null,
        ]);
        exit;
    }
    $id = (int) $matches[1];
}

$allowedMethods = $id === null ? ['GET', 'POST'] : ['GET', 'PUT', 'DELETE'];
if (!in_array($method, $allowedMethods, true)) {
    header('Allow: ' . implode(', ', $allowedMethods));
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
        if ($id === null) {
            $controller->index();
        } else {
            $controller->show($id);
        }
    } elseif ($method === 'POST') {
        $controller->store();
    } elseif ($method === 'PUT') {
        $controller->update($id);
    } else {
        $controller->destroy($id);
    }
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
