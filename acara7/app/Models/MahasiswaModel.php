<?php

namespace App\Models;

require_once __DIR__ . '/Model.php';

class MahasiswaModel extends Model
{
    public function prodiAll(): array
    {
        return $this->db->query(
            'SELECT id, kode, nama FROM prodi ORDER BY nama'
        )->fetchAll();
    }

    public function all(): array
    {
        $query = $this->db->query(
            'SELECT mahasiswa.id, mahasiswa.nim, mahasiswa.nama, mahasiswa.email,
                    prodi.nama AS prodi, mahasiswa.angkatan, mahasiswa.status
             FROM mahasiswa
             INNER JOIN prodi ON prodi.id = mahasiswa.prodi_id
             ORDER BY mahasiswa.id'
        );

        return $query->fetchAll();
    }

    public function find(int $id): ?array
    {
        $query = $this->db->prepare(
            'SELECT mahasiswa.id, mahasiswa.nim, mahasiswa.nama, mahasiswa.email,
                    prodi.nama AS prodi, mahasiswa.angkatan, mahasiswa.status
             FROM mahasiswa
             INNER JOIN prodi ON prodi.id = mahasiswa.prodi_id
             WHERE mahasiswa.id = :id'
        );
        $query->execute(['id' => $id]);
        $mahasiswa = $query->fetch();

        return $mahasiswa === false ? null : $mahasiswa;
    }

    public function create(array $data): void
    {
        $query = $this->db->prepare(
            'INSERT INTO mahasiswa (nim, nama, email, prodi_id, angkatan, status)
             VALUES (:nim, :nama, :email, :prodi_id, :angkatan, :status)'
        );
        $query->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'prodi_id' => $data['prodi_id'],
            'angkatan' => $data['angkatan'],
            'status' => $data['status'] ?? 'aktif',
        ]);
    }
}