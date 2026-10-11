<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\MahasiswaRepository;
use App\Models\ProdiRepository;
use App\Services\MahasiswaService;
use PDOException;

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
        $search = trim($_GET['search'] ?? '');
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
    }

    public function create(): void
    {
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
    }

    public function show(int $id): void
    {
        $item = $this->repository->find($id);

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

    public function edit(int $id): void
    {
        $item = $this->repository->find($id);
        if ($item === null) {
            $this->notFound();
            return;
        }

        $prodi = $this->prodiRepository->all();
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
        if ($this->repository->find($id) === null) {
            $this->notFound();
            return;
        }

        $this->save($id);
    }

    public function destroy(int $id): void
    {
        if ($this->repository->find($id) === null) {
            $this->notFound();
            return;
        }

        $this->repository->delete($id);
        $this->setFlash('success', 'Data mahasiswa berhasil dihapus.');
        $this->redirect(BASE_URL . '/mahasiswa');
    }

    private function save(?int $id = null): void
    {
        try {
            $result = $id === null
                ? $this->service->create($_POST)
                : $this->service->update($id, $_POST);
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->setFlash('danger', 'Data gagal disimpan. Silakan coba kembali.');
            $this->redirect(
                $id === null
                    ? BASE_URL . '/mahasiswa/create'
                    : BASE_URL . '/mahasiswa/' . $id . '/edit'
            );
        }

        if (!$result['success']) {
            http_response_code(422);
            $formData = $result['formData'];
            $errors = $result['errors'];
            $duplicateNimMessage = 'NIM tersebut sudah terdaftar.';
            if (in_array($duplicateNimMessage, $errors, true)) {
                $this->setFlash('danger', $duplicateNimMessage);
                $errors = array_values(array_diff($errors, [$duplicateNimMessage]));
            }
            $isEditing = $id !== null;
            $editingId = $id;
            $this->view('layouts/main', [
                'title' => $isEditing ? 'Edit Mahasiswa | SI Akademik' : 'Tambah Mahasiswa | SI Akademik',
                'content' => __DIR__ . '/../Views/mahasiswa/create.php',
                'prodi' => $this->prodiRepository->all(),
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
}