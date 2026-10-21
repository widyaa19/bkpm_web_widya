<?php

namespace App\Controllers;

use App\Models\MatakuliahModel;
use App\Models\ProdiModel;
use PDOException;

class MatakuliahController
{
    private MatakuliahModel $model;
    private ProdiModel $prodiModel;

    public function __construct()
    {
        $this->model = new MatakuliahModel();
        $this->prodiModel = new ProdiModel();
    }

    public function index(): void
    {
        $perPage = 10;
        $total = $this->model->count();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);
        $matakuliah = $this->model->paginate($perPage, ($page - 1) * $perPage);
        $title = 'Data Mata Kuliah | SI Akademik';
        $content = __DIR__ . '/../Views/matakuliah/index.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function create(): void
    {
        $prodi = $this->prodiModel->all();
        $formData = [];
        $isEditing = false;
        $title = 'Tambah Mata Kuliah | SI Akademik';
        $content = __DIR__ . '/../Views/matakuliah/form.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function edit(int $id): void
    {
        $item = $this->model->find($id);
        if ($item === null) {
            $this->notFound();
            return;
        }

        $prodi = $this->prodiModel->all();
        $formData = $item;
        $isEditing = true;
        $editingId = $id;
        $title = 'Edit Mata Kuliah | SI Akademik';
        $content = __DIR__ . '/../Views/matakuliah/form.php';
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
            $_SESSION['flash'] = 'Mata kuliah berhasil dihapus.';
        } catch (PDOException $exception) {
            $_SESSION['flash'] = 'Mata kuliah tidak dapat dihapus karena masih digunakan.';
            $_SESSION['flash_type'] = 'warning';
        }

        header('Location: ' . BASE_URL . '/matakuliah');
        exit;
    }

    private function save(?int $id = null): void
    {
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        $nama = trim($_POST['nama'] ?? '');
        $sks = (int) ($_POST['sks'] ?? 0);
        $prodiId = (int) ($_POST['prodi_id'] ?? 0);
        $prodi = $this->prodiModel->all();
        $prodiIds = array_map(static fn (array $item): int => (int) $item['id'], $prodi);
        $errors = [];

        if ($kode === '' || mb_strlen($kode) > 10) {
            $errors[] = 'Kode mata kuliah wajib diisi dan maksimal 10 karakter.';
        }
        if ($nama === '' || mb_strlen($nama) > 150) {
            $errors[] = 'Nama mata kuliah wajib diisi dan maksimal 150 karakter.';
        }
        if ($kode !== '' && $nama !== '' && $this->model->duplicateExists($kode, $nama, $id)) {
            $errors[] = 'Kode atau nama mata kuliah tersebut sudah terdaftar.';
        }
        if ($sks < 1 || $sks > 24) {
            $errors[] = 'Jumlah SKS harus antara 1 dan 24.';
        }
        if (!in_array($prodiId, $prodiIds, true)) {
            $errors[] = 'Pilih program studi yang tersedia.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $formData = compact('kode', 'nama', 'sks', 'prodiId');
            $isEditing = $id !== null;
            $editingId = $id;
            $title = $isEditing ? 'Edit Mata Kuliah | SI Akademik' : 'Tambah Mata Kuliah | SI Akademik';
            $content = __DIR__ . '/../Views/matakuliah/form.php';
            require __DIR__ . '/../Views/layouts/main.php';
            return;
        }

        $data = compact('kode', 'nama', 'sks');
        $data['prodi_id'] = $prodiId;
        if ($id === null) {
            $this->model->create($data);
            $_SESSION['flash'] = 'Mata kuliah berhasil ditambahkan.';
        } else {
            $this->model->update($id, $data);
            $_SESSION['flash'] = 'Mata kuliah berhasil diperbarui.';
        }

        header('Location: ' . BASE_URL . '/matakuliah');
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 - Mata kuliah tidak ditemukan';
    }
}