<?php

require_once __DIR__ . '/../app/Models/Mahasiswa.php';

use App\Models\Mahasiswa;

$page = $_GET['page'] ?? 'index';

if ($page === 'create') {
	$title = 'Tambah Mahasiswa | SI Akademik';
	$content = __DIR__ . '/../app/Views/mahasiswa/create.php';
	require __DIR__ . '/../app/Views/layouts/main.php';
	exit;
}

$mahasiswa = [
	new Mahasiswa('230001', 'Andi Setiawan', 'Teknik Informatika'),
	new Mahasiswa('230002', 'Siti Rahma', 'Manajemen Informatika'),
	new Mahasiswa('230003', 'Budi Santoso', 'Teknik Komputer'),
	new Mahasiswa('260004', 'Ani Pratiwi', 'Teknik Informatika'),
];

$title = 'Data Mahasiswa | SI Akademik';
$content = __DIR__ . '/../app/Views/mahasiswa/index.php';
require __DIR__ . '/../app/Views/layouts/main.php';
