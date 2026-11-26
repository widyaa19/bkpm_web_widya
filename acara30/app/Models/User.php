<?php

namespace App\Models;

use InvalidArgumentException;
use PDO;

class User extends BaseModel
{
    public function create(array $data): string
    {
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || strlen($username) > 50) {
            throw new InvalidArgumentException('Username wajib diisi dan maksimal 50 karakter.');
        }
        if ($email === '' || strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email wajib berupa alamat email valid dan maksimal 100 karakter.');
        }
        if (!is_string($password) || $password === '') {
            throw new InvalidArgumentException('Password wajib diisi.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO users (username, email, password, role)
             VALUES (:username, :email, :password, :role)'
        );
        $statement->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':role' => 'user',
        ]);

        return $this->db->lastInsertId();
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, username, email, password, role
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $statement->execute([':username' => $username]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }
}
