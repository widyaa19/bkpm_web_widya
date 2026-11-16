<?php

namespace App\Models;

require_once __DIR__ . '/Model.php';

use InvalidArgumentException;

class Buku extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT id, judul, penulis, penerbit, tahun_terbit, stok
             FROM buku
             ORDER BY id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $query = $this->db->prepare(
            'SELECT id, judul, penulis, penerbit, tahun_terbit, stok
             FROM buku
             WHERE id = :id'
        );
        $query->execute(['id' => $id]);
        $buku = $query->fetch();

        return $buku === false ? null : $buku;
    }

    public function create(array $input): int
    {
        $data = $this->validate($input);
        $query = $this->db->prepare(
            'INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok)
             VALUES (:judul, :penulis, :penerbit, :tahun_terbit, :stok)'
        );
        $query->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $input): void
    {
        $data = $this->validate($input);
        $data['id'] = $id;
        $query = $this->db->prepare(
            'UPDATE buku
             SET judul = :judul, penulis = :penulis, penerbit = :penerbit,
                 tahun_terbit = :tahun_terbit, stok = :stok
             WHERE id = :id'
        );
        $query->execute($data);
    }

    public function delete(int $id): void
    {
        $query = $this->db->prepare('DELETE FROM buku WHERE id = :id');
        $query->execute(['id' => $id]);
    }

    private function validate(array $input): array
    {
        $data = [
            'judul' => $this->stringValue($input['judul'] ?? null),
            'penulis' => $this->stringValue($input['penulis'] ?? null),
            'penerbit' => $this->stringValue($input['penerbit'] ?? null),
            'tahun_terbit' => filter_var($input['tahun_terbit'] ?? null, FILTER_VALIDATE_INT),
            'stok' => filter_var($input['stok'] ?? null, FILTER_VALIDATE_INT),
        ];
        $errors = [];

        foreach (['judul' => 150, 'penulis' => 100, 'penerbit' => 100] as $field => $maxLength) {
            if ($data[$field] === '' || mb_strlen($data[$field]) > $maxLength) {
                $errors[] = ucfirst($field) . " wajib diisi dan maksimal {$maxLength} karakter.";
            }
        }

        if (!is_int($data['tahun_terbit']) || $data['tahun_terbit'] < 1900 || $data['tahun_terbit'] > 2155) {
            $errors[] = 'Tahun terbit harus berupa angka antara 1900 dan 2155.';
        }
        if (!is_int($data['stok']) || $data['stok'] < 0) {
            $errors[] = 'Stok harus berupa bilangan bulat nol atau lebih.';
        }

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return $data;
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
