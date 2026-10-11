<?php

namespace App\Controllers;

use App\Models\Mahasiswa;
use App\Models\MahasiswaRepository;
use InvalidArgumentException;

class MahasiswaController
{
    private MahasiswaRepository $repository;

    public function __construct(MahasiswaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $perPage = 10;
        $total = $this->repository->count($search);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);
        $mahasiswa = $this->repository->all($search, $perPage, ($page - 1) * $perPage);

        $title = 'Data Mahasiswa | SI Akademik';
        $content = __DIR__ . '/../Views/mahasiswa/index.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function create(): void
    {
        $prodi = $this->repository->prodiAll();
        $formData = [];
        $isEditing = false;
        $title = 'Tambah Mahasiswa | SI Akademik';
        $content = __DIR__ . '/../Views/mahasiswa/create.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function show(int $id): void
    {
        $item = $this->repository->find($id);

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
        $item = $this->repository->find($id);
        if ($item === null) {
            $this->notFound();
            return;
        }

        $prodi = $this->repository->prodiAll();
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
        $prodi = $this->repository->prodiAll();
        $prodiIds = array_map(static fn (array $item): int => (int) $item['id'], $prodi);
        $errors = [];
        $mahasiswa = new Mahasiswa();

        $setters = [
            fn () => $mahasiswa->setNim($nim),
            fn () => $mahasiswa->setNama($nama),
            fn () => $mahasiswa->setEmail($email),
            fn () => $mahasiswa->setProdiId($prodiId),
            fn () => $mahasiswa->setAngkatan($angkatan),
            fn () => $mahasiswa->setStatus($status),
        ];
        foreach ($setters as $setter) {
            try {
                $setter();
            } catch (InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $nim = $mahasiswa->getNim();
        $nama = $mahasiswa->getNama();
        if ($nim !== '' && $this->repository->nimExists($nim, $id)) {
            $errors[] = 'NIM tersebut sudah terdaftar.';
        }
        if (!in_array($prodiId, $prodiIds, true)) {
            $errors[] = 'Pilih program studi yang tersedia.';
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

        if ($id === null) {
            $this->repository->create($mahasiswa);
            $_SESSION['flash'] = 'Data mahasiswa berhasil ditambahkan.';
        } else {
            $this->repository->update($id, $mahasiswa);
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