<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\MahasiswaRepository;
use App\Services\MahasiswaService;
use JsonException;
use Throwable;

class ApiMahasiswaController extends BaseController
{
    public function __construct(
        private MahasiswaRepository $repository,
        private MahasiswaService $service
    ) {
    }

    public function index(): void
    {
        if (array_key_exists('id', $_GET)) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if (!is_int($id) || $id < 1) {
                $this->respond(400, false, 'ID mahasiswa harus berupa bilangan bulat positif.', null);
                return;
            }

            try {
                $mahasiswa = $this->repository->find($id);
            } catch (Throwable $exception) {
                $this->logException($exception, 'API mahasiswa GET');
                $this->respond(500, false, 'Data mahasiswa gagal diambil.', null);
                return;
            }

            if ($mahasiswa === null) {
                $this->respond(404, false, 'Data mahasiswa tidak ditemukan.', null);
                return;
            }

            $this->respond(200, true, 'Data berhasil diambil.', $mahasiswa);
            return;
        }

        try {
            $mahasiswa = $this->repository->allForApi();
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa GET');
            $this->respond(500, false, 'Data mahasiswa gagal diambil.', null);
            return;
        }

        $this->respond(200, true, 'Data berhasil diambil.', $mahasiswa);
    }

    public function store(): void
    {
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($contentType !== 'application/json') {
            $this->respond(415, false, 'Content-Type harus application/json.', null);
            return;
        }

        $requestBody = file_get_contents('php://input');
        if ($requestBody === false) {
            $this->respond(400, false, 'Body request tidak dapat dibaca.', null);
            return;
        }

        try {
            $input = json_decode($requestBody, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->respond(400, false, 'Body request bukan JSON yang valid.', null);
            return;
        }

        if (!is_object($input)) {
            $this->respond(400, false, 'Body JSON harus berupa object.', null);
            return;
        }

        try {
            $result = $this->service->create((array) $input);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa POST');
            $this->respond(500, false, 'Data mahasiswa gagal disimpan.', null);
            return;
        }

        if (!$result['success']) {
            $this->respond(422, false, 'Data mahasiswa tidak valid.', null, $result['errors']);
            return;
        }

        try {
            $mahasiswa = $this->repository->findByNim($result['formData']['nim']);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa POST response');
            $this->respond(500, false, 'Data berhasil disimpan tetapi gagal dibaca kembali.', null);
            return;
        }

        if ($mahasiswa === null) {
            $this->respond(500, false, 'Data berhasil disimpan tetapi gagal dibaca kembali.', null);
            return;
        }

        $this->respond(201, true, 'Data mahasiswa berhasil ditambahkan.', $mahasiswa);
    }

    private function respond(
        int $statusCode,
        bool $success,
        string $message,
        mixed $data,
        array $errors = []
    ): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
        if ($errors !== []) {
            $response['errors'] = $errors;
        }

        try {
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            error_log('API mahasiswa JSON encoding failed: ' . $exception->getMessage());
            http_response_code(500);
            echo '{"success":false,"message":"Response JSON gagal dibuat.","data":null}';
        }
    }

    private function logException(Throwable $exception, string $context): void
    {
        error_log(sprintf(
            '%s [%s] %s: %s',
            date('Y-m-d H:i:s'),
            $context,
            get_class($exception),
            $exception->getMessage()
        ));
    }
}
