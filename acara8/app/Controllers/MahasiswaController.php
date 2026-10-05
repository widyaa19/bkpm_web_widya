<?php

namespace App\Controllers;

use App\Models\MahasiswaModel;

class MahasiswaController
{
    private MahasiswaModel $model;

    public function __construct()
    {
        $this->model = new MahasiswaModel();
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $perPage = 10;
        $total = $this->model->count($search);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);
        $mahasiswa = $this->model->all($search, $perPage, ($page - 1) * $perPage);

        $title = 'Data Mahasiswa | SI Akademik';
        $content = __DIR__ . '/../Views/mahasiswa/index.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function create(): void
    {
        $prodi = $this->model->prodiAll();
        $formData = [];
        $isEditing = false;
        $title = 'Tambah Mahasiswa | SI Akademik';
        $content = __DIR__ . '/../Views/mahasiswa/create.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function show(int $id): void
    {
        $item = $this->model->find($id);

        if ($item === null) {
            http_response_code(404);
            echo '404 - Data mahasiswa tidak ditemukan';
            return;
        }

        $title = 'Detail Mahasiswa | SI Akademik';
        $content = __DIR__ . '/../Views/mahasiswa/show.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function store(): void
    {
        $this->save();
    }

    public function edit(int $id): void
    {
        $item = $this->model->find($id);
        if ($item === null) {
            $this->notFound();
            return;
        }

        $prodi = $this->model->prodiAll();
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
        $title = 'Edit Mahasiswa | SI Akademik';
        $content = __DIR__ . '/../Views/mahasiswa/create.php';
        require __DIR__ . '/../Views/layouts/main.php';
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

        $this->model->delete($id);
        $_SESSION['flash'] = 'Data mahasiswa berhasil dihapus.';
        header('Location: ' . BASE_URL . '/mahasiswa');
        exit;
    }

    private function save(?int $id = null): void
    {
        $nim = trim($_POST['nim'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $prodiId = (int) ($_POST['prodi_id'] ?? 0);
        $angkatan = (int) ($_POST['angkatan'] ?? 0);
        $status = $_POST['status'] ?? 'aktif';
        $prodi = $this->model->prodiAll();
        $prodiIds = array_map(static fn (array $item): int => (int) $item['id'], $prodi);
        $errors = [];

        if ($nim === '' || mb_strlen($nim) > 20) {
            $errors[] = 'NIM wajib diisi dan maksimal 20 karakter.';
        } elseif ($this->model->nimExists($nim, $id)) {
            $errors[] = 'NIM tersebut sudah terdaftar.';
        }
        if ($nama === '' || mb_strlen($nama) > 100) {
            $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            $errors[] = 'Email wajib berupa alamat email yang valid (maksimal 100 karakter).';
        }
        if (!in_array($prodiId, $prodiIds, true)) {
            $errors[] = 'Pilih program studi yang tersedia.';
        }
        if ($angkatan < 2000 || $angkatan > 2155) {
            $errors[] = 'Angkatan harus berada antara 2000 dan 2155.';
        }
        if (!in_array($status, ['aktif', 'cuti', 'lulus'], true)) {
            $errors[] = 'Status mahasiswa tidak valid.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $formData = compact('nim', 'nama', 'email', 'prodiId', 'angkatan', 'status');
            $isEditing = $id !== null;
            $editingId = $id;
            $title = $isEditing ? 'Edit Mahasiswa | SI Akademik' : 'Tambah Mahasiswa | SI Akademik';
            $content = __DIR__ . '/../Views/mahasiswa/create.php';
            require __DIR__ . '/../Views/layouts/main.php';
            return;
        }

        $data = [
            'nim' => $nim,
            'nama' => $nama,
            'email' => $email,
            'prodi_id' => $prodiId,
            'angkatan' => $angkatan,
            'status' => $status,
        ];
        if ($id === null) {
            $this->model->create($data);
            $_SESSION['flash'] = 'Data mahasiswa berhasil ditambahkan.';
        } else {
            $this->model->update($id, $data);
            $_SESSION['flash'] = 'Data mahasiswa berhasil diperbarui.';
        }

        header('Location: ' . BASE_URL . '/mahasiswa');
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 - Data mahasiswa tidak ditemukan';
    }
}