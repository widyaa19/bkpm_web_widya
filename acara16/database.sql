CREATE DATABASE IF NOT EXISTS si_akademik
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE si_akademik;

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

INSERT IGNORE INTO prodi (id, kode, nama)
VALUES
    (1, 'TI', 'Teknik Informatika'),
    (2, 'SI', 'Sistem Informasi'),
    (3, 'TK', 'Teknik Komputer');
