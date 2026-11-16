CREATE DATABASE IF NOT EXISTS si_akademik
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE si_akademik;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS prodi (
    id INT NOT NULL AUTO_INCREMENT,
    kode VARCHAR(10) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY kode (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT NOT NULL AUTO_INCREMENT,
    nim VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    prodi_id INT NOT NULL,
    angkatan YEAR NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('aktif', 'cuti', 'lulus') NOT NULL DEFAULT 'aktif',
    PRIMARY KEY (id),
    UNIQUE KEY nim (nim),
    KEY fk_mahasiswa_prodi (prodi_id),
    CONSTRAINT fk_mahasiswa_prodi FOREIGN KEY (prodi_id)
        REFERENCES prodi (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS matakuliah (
    id INT NOT NULL AUTO_INCREMENT,
    kode VARCHAR(10) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    sks TINYINT NOT NULL,
    prodi_id INT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY kode (kode),
    KEY fk_matakuliah_prodi (prodi_id),
    CONSTRAINT fk_matakuliah_prodi FOREIGN KEY (prodi_id)
        REFERENCES prodi (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS buku (
    id INT NOT NULL AUTO_INCREMENT,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit SMALLINT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_buku_judul_penerbit (judul, penerbit)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO prodi (id, kode, nama)
VALUES
    (1, 'TI', 'Teknik Informatika'),
    (2, 'SI', 'Sistem Informasi'),
    (3, 'TK', 'Teknik Komputer');

INSERT IGNORE INTO buku (judul, penulis, penerbit, tahun_terbit, stok)
VALUES
    ('Pemrograman Web dengan PHP', 'Andi Wijaya', 'Informatika', 2025, 10),
    ('Dasar-Dasar MySQL', 'Siti Rahma', 'Elex Media Komputindo', 2024, 8),
    ('Belajar REST API', 'Budi Santoso', 'Andi Publisher', 2026, 12);
