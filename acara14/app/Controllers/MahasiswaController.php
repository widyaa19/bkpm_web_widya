<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\MahasiswaRepository;
use App\Models\ProdiRepository;
use App\Services\MahasiswaService;
use Throwable;

class MahasiswaController extends BaseController
{
    public function __construct(
        private MahasiswaRepository $repository,
        private ProdiRepository $prodiRepository,
        private MahasiswaService $service
    ) {
    }

    public function index(): void
    {
        try {
            $search = trim(\is_scalar($_GET['search'] ?? null) ? (string) $_GET['search'] : '');
            $perPage = 10;
            $total = $this->repository->count($search);
            $pages = max(1, (int) ceil($total / $perPage));
            $page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);
            $mahasiswa = $this->repository->all($search, $perPage, ($page - 1) * $perPage);

            $this->view('layouts/main', [
                'title' => 'Data Mahasiswa | SI Akademik',
                'content' => __DIR__ . '/../Views/mahasiswa/index.php',
                'search' => $search,
                'pages' => $pages,
                'page' => $page,
                'mahasiswa' => $mahasiswa,
            ]);
        } catch (Throwable $exception) {
            $this->showFailure($exception, 'Daftar mahasiswa gagal dimuat.');
        }
    }

    public function create(): void
    {
        try {
            $prodi = $this->prodiRepository->all();
            $formData = [];
            $isEditing = false;
            $this->view('layouts/main', [
                'title' => 'Tambah Mahasiswa | SI Akademik',
                'content' => __DIR__ . '/../Views/mahasiswa/create.php',
                'prodi' => $prodi,
                'formData' => $formData,
                'isEditing' => $isEditing,
            ]);
        } catch (Throwable $exception) {
            $this->showFailure($exception, 'Form mahasiswa gagal dimuat.');
        }
    }

    public function show(int $id): void
    {
        try {
            $item = $this->repository->find($id);
        } catch (Throwable $exception) {
            $this->showFailure($exception, 'Data mahasiswa gagal dimuat.');
            return;
        }

        if ($item === null) {
            http_response_code(404);
            echo '404 - Data mahasiswa tidak ditemukan';
            return;
        }

        $this->view('layouts/main', [
            'title' => 'Detail Mahasiswa | SI Akademik',
            'content' => __DIR__ . '/../Views/mahasiswa/show.php',
            'item' => $item,
            'id' => $id,
        ]);
    }

    public function store(): void
    {
        $this->save();
    }

    public function edit(?int $id = null): void
    {
        if ($id === null) {
            $queryId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
            $id = \is_int($queryId) && $queryId > 0 ? $queryId : 0;
        }
        if ($id < 1) {
            $this->notFound();
            return;
        }

        try {
            $item = $this->repository->find($id);
        } catch (Throwable $exception) {
            $this->showFailure($exception, 'Data mahasiswa gagal dimuat.');
            return;
        }
        if ($item === null) {
            $this->notFound();
            return;
        }

        try {
            $prodi = $this->prodiRepository->all();
        } catch (Throwable $exception) {
            $this->showFailure($exception, 'Form mahasiswa gagal dimuat.');
            return;
        }
        $formData = [
            'nim' => $item['nim'],
            'nama' => $item['nama'],
            'email' => $item['email'],
            'prodiId' => (int) $item['prodi_id'],
            'angkatan' => $item['angkatan'],
            'status' => $item['status'],
        ];
        $isEditing = true;
        $editingId = $id;
        $this->view('layouts/main', [
            'title' => 'Edit Mahasiswa | SI Akademik',
            'content' => __DIR__ . '/../Views/mahasiswa/create.php',
            'prodi' => $prodi,
            'formData' => $formData,
            'isEditing' => $isEditing,
            'editingId' => $editingId,
        ]);
    }

    public function update(int $id): void
    {
        try {
            $item = $this->repository->find($id);
        } catch (Throwable $exception) {
            $this->showFailure($exception, 'Data mahasiswa gagal dimuat.');
            return;
        }

        if ($item === null) {
            $this->notFound();
            return;
        }

        $this->save($id);
    }

    public function destroy(int $id): void
    {
        try {
            $item = $this->repository->find($id);
        } catch (Throwable $exception) {
            $this->showFailure($exception, 'Data mahasiswa gagal dihapus.');
            return;
        }

        if ($item === null) {
            $this->notFound();
            return;
        }

        try {
            $this->repository->delete($id);
            $this->setFlash('success', 'Data mahasiswa berhasil dihapus.');
            $this->redirect(BASE_URL . '/mahasiswa');
        } catch (Throwable $exception) {
            $this->logException($exception, 'Mahasiswa delete');
            $this->setFlash('danger', 'Data gagal dihapus. Silakan coba kembali.');
            $this->redirect(BASE_URL . '/mahasiswa');
        }
    }

    public function delete(): void
    {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!\is_int($id) || $id < 1) {
            $this->notFound();
            return;
        }

        $this->destroy($id);
    }

    private function save(?int $id = null): void
    {
        try {
            $result = $id === null
                ? $this->service->create($_POST)
                : $this->service->update($id, $_POST);
        } catch (Throwable $exception) {
            $this->logException($exception, $id === null ? 'Mahasiswa create' : 'Mahasiswa update');
            $this->setFlash('danger', 'Data gagal disimpan. Silakan coba kembali.');
            $this->redirect(
                $id === null
                    ? BASE_URL . '/mahasiswa/create'
                    : BASE_URL . '/mahasiswa/' . $id . '/edit'
            );
            return;
        }

        if (!$result['success']) {
            http_response_code(422);
            $formData = $result['formData'];
            $errors = $result['errors'];
            $duplicateNimMessage = 'NIM tersebut sudah terdaftar.';
            if (\in_array($duplicateNimMessage, $errors, true)) {
                $this->setFlash('danger', $duplicateNimMessage);
                $errors = array_values(array_diff($errors, [$duplicateNimMessage]));
            }
            $isEditing = $id !== null;
            $editingId = $id;
            try {
                $prodi = $this->prodiRepository->all();
            } catch (Throwable $exception) {
                $this->showFailure($exception, 'Form mahasiswa gagal dimuat.');
                return;
            }
            $this->view('layouts/main', [
                'title' => $isEditing ? 'Edit Mahasiswa | SI Akademik' : 'Tambah Mahasiswa | SI Akademik',
                'content' => __DIR__ . '/../Views/mahasiswa/create.php',
                'prodi' => $prodi,
                'formData' => $formData,
                'isEditing' => $isEditing,
                'editingId' => $editingId,
                'errors' => $errors,
            ]);
            return;
        }

        if ($id === null) {
            $this->setFlash('success', 'Data mahasiswa berhasil ditambahkan.');
        } else {
            $this->setFlash('success', 'Data mahasiswa berhasil diubah.');
        }

        $this->redirect(BASE_URL . '/mahasiswa');
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 - Data mahasiswa tidak ditemukan';
    }

    private function showFailure(Throwable $exception, string $message): void
    {
        $this->logException($exception, 'Mahasiswa request');
        http_response_code(500);
        $this->setFlash('danger', $message);
        $this->view('layouts/main', [
            'title' => 'Terjadi Kesalahan | SI Akademik',
            'content' => __DIR__ . '/../Views/mahasiswa/error.php',
        ]);
    }

    private function logException(Throwable $exception, string $context): void
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

        $entry = \sprintf(
            "%s [%s] %s: %s%s",
            date('Y-m-d H:i:s'),
            $context,
            \get_class($exception),
            $exception->getMessage(),
            PHP_EOL
        );
        if (error_log($entry, 3, $logDirectory . '/app.log') !== true) {
            error_log('Unable to write application log file.');
        }
    }
}