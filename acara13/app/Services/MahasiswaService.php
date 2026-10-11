<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\MahasiswaRepository;
use App\Models\ProdiRepository;
use InvalidArgumentException;

class MahasiswaService
{
    public function __construct(
        private MahasiswaRepository $mahasiswaRepository,
        private ProdiRepository $prodiRepository
    ) {
    }

    /**
     * @return array{success: bool, errors: array<int, string>, formData: array<string, mixed>}
     */
    public function create(array $input): array
    {
        $result = $this->validate($input);
        if (!$result['success']) {
            return $result;
        }

        $this->mahasiswaRepository->create($result['mahasiswa']);

        return [
            'success' => true,
            'errors' => [],
            'formData' => $result['formData'],
        ];
    }

    /**
     * @return array{success: bool, errors: array<int, string>, formData: array<string, mixed>}
     */
    public function update(int $id, array $input): array
    {
        $result = $this->validate($input, $id);
        if (!$result['success']) {
            return $result;
        }

        $this->mahasiswaRepository->update($id, $result['mahasiswa']);

        return [
            'success' => true,
            'errors' => [],
            'formData' => $result['formData'],
        ];
    }

    /**
     * @return array{success: bool, errors: array<int, string>, formData: array<string, mixed>, mahasiswa?: Mahasiswa}
     */
    private function validate(array $input, ?int $exceptId = null): array
    {
        $formData = [
            'nim' => $this->stringValue($input['nim'] ?? null),
            'nama' => $this->stringValue($input['nama'] ?? null),
            'email' => $this->stringValue($input['email'] ?? null),
            'prodiId' => $this->integerValue($input['prodi_id'] ?? null),
            'angkatan' => $this->integerValue($input['angkatan'] ?? null),
            'status' => $this->stringValue($input['status'] ?? 'aktif'),
        ];

        $mahasiswa = new Mahasiswa();
        $errors = [];
        $setters = [
            'NIM' => static fn () => $mahasiswa->setNim($formData['nim']),
            'Nama' => static fn () => $mahasiswa->setNama($formData['nama']),
            'Email' => static fn () => $mahasiswa->setEmail($formData['email']),
            'Program studi' => static fn () => $mahasiswa->setProdiId($formData['prodiId']),
            'Angkatan' => static fn () => $mahasiswa->setAngkatan($formData['angkatan']),
            'Status' => static fn () => $mahasiswa->setStatus($formData['status']),
        ];

        foreach ($setters as $setter) {
            try {
                $setter();
            } catch (InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if (
            $formData['prodiId'] > 0
            && !$this->prodiRepository->exists($formData['prodiId'])
        ) {
            $errors[] = 'Pilih program studi yang tersedia.';
        }

        $nim = $formData['nim'];
        if (
            $nim !== ''
            && strlen($nim) <= 20
            && ctype_digit($nim)
            && $this->mahasiswaRepository->nimExists($nim, $exceptId)
        ) {
            $errors[] = 'NIM tersebut sudah terdaftar.';
        }

        if ($errors !== []) {
            return [
                'success' => false,
                'errors' => $errors,
                'formData' => $formData,
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'formData' => $formData,
            'mahasiswa' => $mahasiswa,
        ];
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function integerValue(mixed $value): int
    {
        if (!is_scalar($value)) {
            return 0;
        }

        $integer = filter_var((string) $value, FILTER_VALIDATE_INT);

        return $integer === false ? 0 : $integer;
    }
}
