<?php

namespace App\Controllers;

use App\Models\ProdiModel;
use PDOException;

class ProdiController
{
    private ProdiModel $model;

    public function __construct()
    {
        $this->model = new ProdiModel();
    }

    public function index(): void
    {
        $perPage = 10;
        $total = $this->model->count();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);
        $prodi = $this->model->paginate($perPage, ($page - 1) * $perPage);
        $title = 'Data Program Studi | SI Akademik';
        $content = __DIR__ . '/../Views/prodi/index.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function create(): void
    {
        $formData = [];
        $isEditing = false;
        $title = 'Tambah Program Studi | SI Akademik';
        $content = __DIR__ . '/../Views/prodi/form.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function edit(int $id): void
    {
        $item = $this->model->find($id);
        if ($item === null) {
            $this->notFound();
            return;
        }

        $formData = $item;
        $isEditing = true;
        $editingId = $id;
        $title = 'Edit Program Studi | SI Akademik';
        $content = __DIR__ . '/../Views/prodi/form.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function store(): void
    {
        $this->save();
    }

    public function update(int $id): void
    {
        if ($this->model->find($id) === null) {
            $this->notFound();
            return;
        }

        $this->save($id);
    }

    public function destroy(int $id): void
    {
        if ($this->model->find($id) === null) {
            $this->notFound();
            return;
        }

        try {
            $this->model->delete($id);
            $_SESSION['flash'] = 'Program studi berhasil dihapus.';
        } catch (PDOException $exception) {
            $_SESSION['flash'] = 'Program studi tidak dapat dihapus karena masih digunakan.';
            $_SESSION['flash_type'] = 'warning';
        }

        header('Location: ' . BASE_URL . '/prodi');
        exit;
    }

    private function save(?int $id = null): void
    {
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        $nama = trim($_POST['nama'] ?? '');
        $errors = [];

        if ($kode === '' || mb_strlen($kode) > 10) {
            $errors[] = 'Kode program studi wajib diisi dan maksimal 10 karakter.';
        }
        if ($nama === '' || mb_strlen($nama) > 100) {
            $errors[] = 'Nama program studi wajib diisi dan maksimal 100 karakter.';
        }
        if ($kode !== '' && $nama !== '' && $this->model->duplicateExists($kode, $nama, $id)) {
            $errors[] = 'Kode atau nama program studi tersebut sudah terdaftar.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $formData = compact('kode', 'nama');
            $isEditing = $id !== null;
            $editingId = $id;
            $title = $isEditing ? 'Edit Program Studi | SI Akademik' : 'Tambah Program Studi | SI Akademik';
            $content = __DIR__ . '/../Views/prodi/form.php';
            require __DIR__ . '/../Views/layouts/main.php';
            return;
        }

        $data = compact('kode', 'nama');
        if ($id === null) {
            $this->model->create($data);
            $_SESSION['flash'] = 'Program studi berhasil ditambahkan.';
        } else {
            $this->model->update($id, $data);
            $_SESSION['flash'] = 'Program studi berhasil diperbarui.';
        }

        header('Location: ' . BASE_URL . '/prodi');
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 - Program studi tidak ditemukan';
    }
}