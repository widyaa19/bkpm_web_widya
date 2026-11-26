<?php

namespace App\Models;

require_once __DIR__ . '/Model.php';

class ProdiModel extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT id, kode, nama FROM prodi ORDER BY nama')->fetchAll();
    }

    public function paginate(int $limit, int $offset): array
    {
        $query = $this->db->prepare('SELECT id, kode, nama FROM prodi ORDER BY nama LIMIT :limit OFFSET :offset');
        $query->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM prodi')->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $query = $this->db->prepare('SELECT id, kode, nama FROM prodi WHERE id = :id');
        $query->execute(['id' => $id]);
        $prodi = $query->fetch();

        return $prodi === false ? null : $prodi;
    }

    public function create(array $data): void
    {
        $query = $this->db->prepare('INSERT INTO prodi (kode, nama) VALUES (:kode, :nama)');
        $query->execute(['kode' => $data['kode'], 'nama' => $data['nama']]);
    }

    public function update(int $id, array $data): void
    {
        $query = $this->db->prepare('UPDATE prodi SET kode = :kode, nama = :nama WHERE id = :id');
        $query->execute(['id' => $id, 'kode' => $data['kode'], 'nama' => $data['nama']]);
    }

    public function delete(int $id): void
    {
        $query = $this->db->prepare('DELETE FROM prodi WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    public function duplicateExists(string $kode, string $nama, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM prodi WHERE (kode = :kode OR nama = :nama)';
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