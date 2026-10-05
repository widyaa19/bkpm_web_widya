<?php

namespace App\Models;

require_once __DIR__ . '/Model.php';

class MatakuliahModel extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT matakuliah.id, matakuliah.kode, matakuliah.nama, matakuliah.sks,
                    prodi.nama AS prodi
             FROM matakuliah
             INNER JOIN prodi ON prodi.id = matakuliah.prodi_id
             ORDER BY matakuliah.kode'
        )->fetchAll();
    }

    public function paginate(int $limit, int $offset): array
    {
        $query = $this->db->prepare(
            'SELECT matakuliah.id, matakuliah.kode, matakuliah.nama, matakuliah.sks,
                    prodi.nama AS prodi
             FROM matakuliah
             INNER JOIN prodi ON prodi.id = matakuliah.prodi_id
             ORDER BY matakuliah.kode LIMIT :limit OFFSET :offset'
        );
        $query->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM matakuliah')->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $query = $this->db->prepare('SELECT id, kode, nama, sks, prodi_id FROM matakuliah WHERE id = :id');
        $query->execute(['id' => $id]);
        $matakuliah = $query->fetch();

        return $matakuliah === false ? null : $matakuliah;
    }

    public function create(array $data): void
    {
        $query = $this->db->prepare(
            'INSERT INTO matakuliah (kode, nama, sks, prodi_id)
             VALUES (:kode, :nama, :sks, :prodi_id)'
        );
        $query->execute([
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'sks' => $data['sks'],
            'prodi_id' => $data['prodi_id'],
        ]);
    }

    public function update(int $id, array $data): void
    {
        $query = $this->db->prepare(
            'UPDATE matakuliah SET kode = :kode, nama = :nama, sks = :sks, prodi_id = :prodi_id
             WHERE id = :id'
        );
        $query->execute([
            'id' => $id,
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'sks' => $data['sks'],
            'prodi_id' => $data['prodi_id'],
        ]);
    }

    public function delete(int $id): void
    {
        $query = $this->db->prepare('DELETE FROM matakuliah WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    public function duplicateExists(string $kode, string $nama, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM matakuliah WHERE (kode = :kode OR nama = :nama)';
        $params = ['kode' => $kode, 'nama' => $nama];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return (int) $query->fetchColumn() > 0;
    }
}