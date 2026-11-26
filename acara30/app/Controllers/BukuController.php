<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\Buku;
use InvalidArgumentException;
use JsonException;
use Throwable;

class BukuController
{
    private Buku $model;

    public function __construct()
    {
        $this->model = new Buku();
    }

    public function index(): void
    {
        $id = $this->requestId();
        if ($id === false) {
            $this->respond(400, false, 'ID buku harus berupa bilangan bulat positif.', null);
            return;
        }

        try {
            if ($id !== null) {
                $buku = $this->model->find($id);
                if ($buku === null) {
                    $this->respond(404, false, 'Data buku tidak ditemukan.', null);
                    return;
                }
                $this->respond(200, true, 'Data buku berhasil diambil.', $buku);
                return;
            }

            $this->respond(200, true, 'Data buku berhasil diambil.', $this->model->all());
        } catch (Throwable $exception) {
            $this->logException($exception, 'API buku GET');
            $this->respond(500, false, 'Data buku gagal diambil.', null);
        }
    }

    public function show(int $id): void
    {
        $this->showById($id);
    }

    public function store(): void
    {
        $input = $this->readJsonBody();
        if ($input === null) {
            return;
        }

        try {
            $id = $this->model->create($input);
            $buku = $this->model->find($id);
            $this->respond(201, true, 'Data buku berhasil ditambahkan.', $buku);
        } catch (InvalidArgumentException $exception) {
            $this->respond(400, false, $exception->getMessage(), null);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API buku POST');
            $this->respond(500, false, 'Data buku gagal disimpan.', null);
        }
    }

    public function update(?int $id = null): void
    {
        $id ??= $this->requestId();
        if (!is_int($id) || $id < 1) {
            $this->respond(400, false, 'ID buku harus berupa bilangan bulat positif.', null);
            return;
        }

        $input = $this->readJsonBody();
        if ($input === null) {
            return;
        }

        try {
            if ($this->model->find($id) === null) {
                $this->respond(404, false, 'Data buku tidak ditemukan.', null);
                return;
            }

            $this->model->update($id, $input);
            $this->respond(200, true, 'Data buku berhasil diperbarui.', $this->model->find($id));
        } catch (InvalidArgumentException $exception) {
            $this->respond(400, false, $exception->getMessage(), null);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API buku PUT');
            $this->respond(500, false, 'Data buku gagal diperbarui.', null);
        }
    }

    public function destroy(?int $id = null): void
    {
        $id ??= $this->requestId();
        if (!is_int($id) || $id < 1) {
            $this->respond(400, false, 'ID buku harus berupa bilangan bulat positif.', null);
            return;
        }

        try {
            $buku = $this->model->find($id);
            if ($buku === null) {
                $this->respond(404, false, 'Data buku tidak ditemukan.', null);
                return;
            }

            $this->model->delete($id);
            $this->respond(200, true, 'Data buku berhasil dihapus.', $buku);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API buku DELETE');
            $this->respond(500, false, 'Data buku gagal dihapus.', null);
        }
    }

    private function showById(int $id): void
    {
        if ($id < 1) {
            $this->respond(400, false, 'ID buku harus berupa bilangan bulat positif.', null);
            return;
        }

        try {
            $buku = $this->model->find($id);
            if ($buku === null) {
                $this->respond(404, false, 'Data buku tidak ditemukan.', null);
                return;
            }

            $this->respond(200, true, 'Data buku berhasil diambil.', $buku);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API buku GET by ID');
            $this->respond(500, false, 'Data buku gagal diambil.', null);
        }
    }

    private function requestId(): int|false|null
    {
        if (!array_key_exists('id', $_GET)) {
            return null;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : false;
    }

    private function readJsonBody(): ?array
    {
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($contentType !== 'application/json') {
            $this->respond(415, false, 'Content-Type harus application/json.', null);
            return null;
        }

        $requestBody = file_get_contents('php://input');
        if ($requestBody === false) {
            $this->respond(400, false, 'Body request tidak dapat dibaca.', null);
            return null;
        }

        try {
            $input = json_decode($requestBody, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->respond(400, false, 'Body request bukan JSON yang valid.', null);
            return null;
        }

        if (!is_object($input)) {
            $this->respond(400, false, 'Body JSON harus berupa object.', null);
            return null;
        }

        return (array) $input;
    }

    private function respond(int $statusCode, bool $success, string $message, mixed $data): void
    {
        Response::json($statusCode, $success, $message, $data);
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
