<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\MahasiswaRepository;
use App\Models\ProdiRepository;
use DomainException;
use InvalidArgumentException;

class MahasiswaService
{
    public function __construct(
        private MahasiswaRepository $mahasiswaRepository,
        private ProdiRepository $prodiRepository
    ) {
    }

    /**
     * @return array{success: bool, errors: array<string, list<string>>, formData: array<string, mixed>}
     */
    public function create(array $input): array
    {
        $result = $this->validate($input);
        if (!$result['success']) {
            return $result;
        }

        try {
            $this->mahasiswaRepository->create($result['mahasiswa']);
        } catch (DomainException $exception) {
            return [
                'success' => false,
                'errors' => ['nim' => [$exception->getMessage()]],
                'formData' => $result['formData'],
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'formData' => $result['formData'],
        ];
    }

    /**
     * @return array{success: bool, errors: array<string, list<string>>, formData: array<string, mixed>}
     */
    public function update(int $id, array $input): array
    {
        $result = $this->validate($input, $id);
        if (!$result['success']) {
            return $result;
        }

        try {
            $this->mahasiswaRepository->update($id, $result['mahasiswa']);
        } catch (DomainException $exception) {
            return [
                'success' => false,
                'errors' => ['nim' => [$exception->getMessage()]],
                'formData' => $result['formData'],
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'formData' => $result['formData'],
        ];
    }

    /**
     * @return array{success: bool, errors: array<string, list<string>>, formData: array<string, mixed>, mahasiswa?: Mahasiswa}
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
            'nim' => static fn () => $mahasiswa->setNim($formData['nim']),
            'nama' => static fn () => $mahasiswa->setNama($formData['nama']),
            'email' => static fn () => $mahasiswa->setEmail($formData['email']),
            'prodi_id' => static fn () => $mahasiswa->setProdiId($formData['prodiId']),
            'angkatan' => static fn () => $mahasiswa->setAngkatan($formData['angkatan']),
            'status' => static fn () => $mahasiswa->setStatus($formData['status']),
        ];

        foreach ($setters as $field => $setter) {
            try {
                $setter();
            } catch (InvalidArgumentException $exception) {
                $errors[$field][] = $exception->getMessage();
            }
        }

        if (
            $formData['prodiId'] > 0
            && !$this->prodiRepository->exists($formData['prodiId'])
        ) {
            $errors['prodi_id'][] = 'Pilih program studi yang tersedia.';
        }

        $nim = $formData['nim'];
        if (
            $nim !== ''
            && strlen($nim) <= 20
            && ctype_digit($nim)
            && $this->mahasiswaRepository->existsByNim($nim, $exceptId)
        ) {
            $errors['nim'][] = 'NIM tersebut sudah terdaftar.';
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
