<?php

namespace App\Models;

require_once __DIR__ . '/BaseModel.php';

class ProdiRepository extends BaseModel
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT id, kode, nama FROM prodi ORDER BY nama'
        )->fetchAll();
    }

    public function exists(int $id): bool
    {
        $query = $this->db->prepare('SELECT COUNT(*) FROM prodi WHERE id = :id');
        $query->execute(['id' => $id]);

        return (int) $query->fetchColumn() > 0;
    }
}
