<?php

namespace App\Models;

class Mahasiswa
{
    private string $nim;
    private string $nama;
    private string $prodi;

    public function __construct(string $nim, string $nama, string $prodi)
    {
        $this->nim = strtoupper(trim($nim));
        $this->nama = trim($nama);
        $this->prodi = trim($prodi);
    }

    public function getNim(): string
    {
        return $this->nim;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function getProdi(): string
    {
        return $this->prodi;
    }

   public function getAngkatan(): string
    {
        return "20" . substr($this->nim, 0, 2);
    }
}