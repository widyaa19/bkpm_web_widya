<?php

namespace App\Models;

use InvalidArgumentException;

class Mahasiswa
{
    private string $nim = '';
    private string $nama = '';
    private string $email = '';
    private int $prodiId = 0;
    private int $angkatan = 0;
    private string $status = 'aktif';

    public function setNim(string $nim): void
    {
        $nim = trim($nim);
        if ($nim === '' || strlen($nim) > 20 || !ctype_digit($nim)) {
            throw new InvalidArgumentException('NIM wajib diisi, maksimal 20 karakter, dan harus berupa angka.');
        }

        $this->nim = $nim;
    }

    public function setNama(string $nama): void
    {
        $nama = trim($nama);
        if ($nama === '' || mb_strlen($nama) > 100) {
            throw new InvalidArgumentException('Nama wajib diisi dan maksimal 100 karakter.');
        }

        $this->nama = $nama;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);
        if (mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email wajib berupa alamat email yang valid (maksimal 100 karakter).');
        }

        $this->email = $email;
    }

    public function setProdiId(int $prodiId): void
    {
        if ($prodiId < 1) {
            throw new InvalidArgumentException('Pilih program studi yang tersedia.');
        }

        $this->prodiId = $prodiId;
    }

    public function setAngkatan(int $angkatan): void
    {
        if ($angkatan < 2000 || $angkatan > 2155) {
            throw new InvalidArgumentException('Angkatan harus berada antara 2000 dan 2155.');
        }

        $this->angkatan = $angkatan;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, ['aktif', 'cuti', 'lulus'], true)) {
            throw new InvalidArgumentException('Status mahasiswa tidak valid.');
        }

        $this->status = $status;
    }

    public function getNim(): string
    {
        return $this->nim;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getProdiId(): int
    {
        return $this->prodiId;
    }

    public function getAngkatan(): int
    {
        return $this->angkatan;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}