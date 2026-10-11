<?php

namespace App\Models;

use App\Core\Database;

require_once __DIR__ . '/BaseModel.php';

class MahasiswaRepository extends BaseModel
{
    public function __construct(Database $database)
    {
        parent::__construct($database);
    }

    public function prodiAll(): array
    {
        return $this->db->query(
            'SELECT id, kode, nama FROM prodi ORDER BY nama'
        )->fetchAll();
    }

    public function all(string $search = '', int $limit = 10, int $offset = 0): array
    {
        $query = $this->db->prepare(
            'SELECT mahasiswa.id, mahasiswa.nim, mahasiswa.nama, mahasiswa.email,
                    mahasiswa.prodi_id, prodi.nama AS prodi, mahasiswa.angkatan, mahasiswa.status
             FROM mahasiswa
             INNER JOIN prodi ON prodi.id = mahasiswa.prodi_id
             WHERE mahasiswa.nim LIKE :search_nim OR mahasiswa.nama LIKE :search_nama
             ORDER BY mahasiswa.id LIMIT :limit OFFSET :offset'
        );
        $query->bindValue(':search_nim', '%' . $search . '%');
        $query->bindValue(':search_nama', '%' . $search . '%');
        $query->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll();
    }

    public function count(string $search): int
    {
        $query = $this->db->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE nim LIKE :search_nim OR nama LIKE :search_nama'
        );
        $query->execute([
            'search_nim' => '%' . $search . '%',
            'search_nama' => '%' . $search . '%',
        ]);

        return (int) $query->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $query = $this->db->prepare(
            'SELECT mahasiswa.id, mahasiswa.nim, mahasiswa.nama, mahasiswa.email,
                    mahasiswa.prodi_id, prodi.nama AS prodi, mahasiswa.angkatan, mahasiswa.status
             FROM mahasiswa
             INNER JOIN prodi ON prodi.id = mahasiswa.prodi_id
             WHERE mahasiswa.id = :id'
        );
        $query->execute(['id' => $id]);
        $mahasiswa = $query->fetch();

        return $mahasiswa === false ? null : $mahasiswa;
    }

    public function create(Mahasiswa $mahasiswa): void
    {
        $query = $this->db->prepare(
            'INSERT INTO mahasiswa (nim, nama, email, prodi_id, angkatan, status)
             VALUES (:nim, :nama, :email, :prodi_id, :angkatan, :status)'
        );
        $query->execute([
            'nim' => $mahasiswa->getNim(),
            'nama' => $mahasiswa->getNama(),
            'email' => $mahasiswa->getEmail(),
            'prodi_id' => $mahasiswa->getProdiId(),
            'angkatan' => $mahasiswa->getAngkatan(),
            'status' => $mahasiswa->getStatus(),
        ]);
    }

    public function update(int $id, Mahasiswa $mahasiswa): void
    {
        $query = $this->db->prepare(
            'UPDATE mahasiswa
             SET nim = :nim, nama = :nama, email = :email, prodi_id = :prodi_id,
                 angkatan = :angkatan, status = :status
             WHERE id = :id'
        );
        $query->execute([
            'id' => $id,
            'nim' => $mahasiswa->getNim(),
            'nama' => $mahasiswa->getNama(),
            'email' => $mahasiswa->getEmail(),
            'prodi_id' => $mahasiswa->getProdiId(),
            'angkatan' => $mahasiswa->getAngkatan(),
            'status' => $mahasiswa->getStatus(),
        ]);
    }

    public function delete(int $id): void
    {
        $query = $this->db->prepare('DELETE FROM mahasiswa WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    public function nimExists(string $nim, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim';
        $params = ['nim' => $nim];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return (int) $query->fetchColumn() > 0;
    }
}
