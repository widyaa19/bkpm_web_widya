<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Response;
use App\Models\MahasiswaRepository;
use App\Services\MahasiswaService;
use JsonException;
use Throwable;

class ApiMahasiswaController extends BaseController
{
    private const MAX_MULTIPART_BODY_SIZE = 3145728;

    private ?array $putPhotoUpload = null;

    public function __construct(
        private MahasiswaRepository $repository,
        private MahasiswaService $service
    ) {
    }

    public function index(): void
    {
        if (array_key_exists('id', $_GET)) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if (!is_int($id) || $id < 1) {
                $this->respond(400, false, 'ID mahasiswa harus berupa bilangan bulat positif.', null);
                return;
            }

            $this->show($id);
            return;
        }

        try {
            $mahasiswa = $this->repository->allForApi();
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa GET');
            $this->respond(500, false, 'Data mahasiswa gagal diambil.', null);
            return;
        }

        $this->respond(200, true, 'Data berhasil diambil.', $mahasiswa);
    }

    public function show(int $id): void
    {
        if ($id < 1) {
            $this->respond(400, false, 'ID mahasiswa harus berupa bilangan bulat positif.', null);
            return;
        }

        try {
            $mahasiswa = $this->repository->find($id);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa GET by ID');
            $this->respond(500, false, 'Data mahasiswa gagal diambil.', null);
            return;
        }

        if ($mahasiswa === null) {
            $this->respond(404, false, 'Data mahasiswa tidak ditemukan.', null);
            return;
        }

        $this->respond(200, true, 'Data berhasil diambil.', $mahasiswa);
    }

    public function store(): void
    {
        Auth::requireAdmin();

        $input = $this->readRequestBody();
        if ($input === null) {
            return;
        }
        if (!$this->validateNimFormat($input)) {
            return;
        }

        $uploadedPhoto = $this->storePhotoUpload();
        $input['foto'] = $uploadedPhoto;

        try {
            $result = $this->service->create($input);
        } catch (Throwable $exception) {
            $this->removePhoto($uploadedPhoto);
            $this->logException($exception, 'API mahasiswa POST');
            $this->respond(500, false, 'Data mahasiswa gagal disimpan.', null);
            return;
        }

        if (!$result['success']) {
            $this->removePhoto($uploadedPhoto);
            $this->respond(422, false, 'Data mahasiswa tidak valid.', null, $result['errors']);
            return;
        }

        try {
            $mahasiswa = $this->repository->findByNim($result['formData']['nim']);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa POST response');
            $this->respond(500, false, 'Data berhasil disimpan tetapi gagal dibaca kembali.', null);
            return;
        }

        if ($mahasiswa === null) {
            $this->respond(500, false, 'Data berhasil disimpan tetapi gagal dibaca kembali.', null);
            return;
        }

        $this->respond(201, true, 'Data mahasiswa berhasil ditambahkan.', $mahasiswa);
    }

    public function update(?int $id = null): void
    {
        Auth::requireAdmin();

        $id ??= $this->queryId();
        if ($id === null) {
            $this->respond(400, false, 'ID mahasiswa harus berupa bilangan bulat positif.', null);
            return;
        }

        if ($id < 1) {
            $this->respond(400, false, 'ID mahasiswa harus berupa bilangan bulat positif.', null);
            return;
        }

        $existingMahasiswa = null;
        try {
            $existingMahasiswa = $this->repository->find($id);
            if ($existingMahasiswa === null) {
                $this->respond(404, false, 'Data mahasiswa tidak ditemukan.', null);
                return;
            }
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa PUT lookup');
            $this->respond(500, false, 'Data mahasiswa gagal diperbarui.', null);
            return;
        }

        $input = $this->readRequestBody();
        if ($input === null) {
            return;
        }
        if (!$this->validateNimFormat($input)) {
            return;
        }

        $uploadedPhoto = $this->storePhotoUpload();
        $input['foto'] = $uploadedPhoto ?? ($existingMahasiswa['foto'] ?? null);

        try {
            $result = $this->service->update($id, $input);
        } catch (Throwable $exception) {
            $this->removePhoto($uploadedPhoto);
            $this->logException($exception, 'API mahasiswa PUT');
            $this->respond(500, false, 'Data mahasiswa gagal diperbarui.', null);
            return;
        }

        if (!$result['success']) {
            $this->removePhoto($uploadedPhoto);
            $this->respond(422, false, 'Data mahasiswa tidak valid.', null, $result['errors']);
            return;
        }

        try {
            $mahasiswa = $this->repository->find($id);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa PUT response');
            $this->respond(500, false, 'Data berhasil diperbarui tetapi gagal dibaca kembali.', null);
            return;
        }

        if ($mahasiswa === null) {
            $this->respond(500, false, 'Data berhasil diperbarui tetapi gagal dibaca kembali.', null);
            return;
        }

        if ($uploadedPhoto !== null) {
            $this->removePhoto($existingMahasiswa['foto'] ?? null);
        }

        $this->respond(200, true, 'Data mahasiswa berhasil diperbarui.', $mahasiswa);
    }

    public function destroy(?int $id = null): void
    {
        Auth::requireAdmin();

        $id ??= $this->queryId();
        if ($id === null) {
            $this->respond(400, false, 'ID mahasiswa harus berupa bilangan bulat positif.', null);
            return;
        }

        if ($id < 1) {
            $this->respond(400, false, 'ID mahasiswa harus berupa bilangan bulat positif.', null);
            return;
        }

        try {
            $mahasiswa = $this->repository->find($id);
            if ($mahasiswa === null) {
                $this->respond(404, false, 'Data mahasiswa tidak ditemukan.', null);
                return;
            }

            $this->repository->delete($id);
            $this->removePhoto($mahasiswa['foto'] ?? null);
        } catch (Throwable $exception) {
            $this->logException($exception, 'API mahasiswa DELETE');
            $this->respond(500, false, 'Data mahasiswa gagal dihapus.', null);
            return;
        }

        $this->respond(200, true, 'Data mahasiswa berhasil dihapus.', $mahasiswa);
    }

    private function readRequestBody(): ?array
    {
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($contentType === 'multipart/form-data') {
            if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'PUT') {
                return $this->readMultipartPutBody();
            }

            return $_POST;
        }

        if ($contentType !== 'application/json') {
            $this->respond(415, false, 'Content-Type harus application/json atau multipart/form-data.', null);
            return null;
        }

        $requestBody = file_get_contents('php://input');
        if ($requestBody === false) {
            $this->respond(400, false, 'Body request tidak dapat dibaca.', null);
            return null;
        }

        try {
            $input = json_decode($requestBody, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->respond(400, false, 'Body request bukan JSON yang valid.', null);
            return null;
        }

        if (!is_object($input)) {
            $this->respond(400, false, 'Body JSON harus berupa object.', null);
            return null;
        }

        return (array) $input;
    }

    private function readMultipartPutBody(): ?array
    {
        $contentLength = filter_var($_SERVER['CONTENT_LENGTH'] ?? null, FILTER_VALIDATE_INT);
        if (is_int($contentLength) && $contentLength > self::MAX_MULTIPART_BODY_SIZE) {
            $this->respond(413, false, 'Ukuran request terlalu besar.', null);
            return null;
        }

        $body = file_get_contents('php://input', false, null, 0, self::MAX_MULTIPART_BODY_SIZE + 1);
        if ($body === false) {
            $this->respond(400, false, 'Body request tidak dapat dibaca.', null);
            return null;
        }
        if (strlen($body) > self::MAX_MULTIPART_BODY_SIZE) {
            $this->respond(413, false, 'Ukuran request terlalu besar.', null);
            return null;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (
            preg_match('/(?:^|;)\s*boundary=(?:"([^"]+)"|([^;\s]+))/i', $contentType, $matches) !== 1
        ) {
            $this->respond(400, false, 'Boundary multipart tidak valid.', null);
            return null;
        }
        $quotedBoundary = $matches[1] ?? '';
        $boundary = $quotedBoundary !== '' ? $quotedBoundary : ($matches[2] ?? '');
        if (strlen($boundary) > 70 || $boundary === '') {
            $this->respond(400, false, 'Boundary multipart tidak valid.', null);
            return null;
        }

        $parts = explode('--' . $boundary, $body);
        if (count($parts) < 3) {
            $this->respond(400, false, 'Body multipart tidak valid.', null);
            return null;
        }

        $input = [];
        foreach (array_slice($parts, 1) as $part) {
            if (str_starts_with($part, '--')) {
                break;
            }

            if (str_starts_with($part, "\r\n")) {
                $part = substr($part, 2);
            }
            if (str_ends_with($part, "\r\n")) {
                $part = substr($part, 0, -2);
            }

            $separator = strpos($part, "\r\n\r\n");
            if ($separator === false) {
                $this->respond(400, false, 'Bagian body multipart tidak valid.', null);
                return null;
            }

            $headers = substr($part, 0, $separator);
            $value = substr($part, $separator + 4);
            if (
                preg_match(
                    '/(?:^|\r\n)Content-Disposition:\s*form-data;\s*name="([^"]+)"([^\r\n]*)/i',
                    $headers,
                    $disposition
                ) !== 1
            ) {
                $this->respond(400, false, 'Bagian body multipart tidak valid.', null);
                return null;
            }

            $name = $disposition[1];
            if (preg_match('/;\s*filename="([^"]*)"/i', $disposition[2], $fileMatch) === 1) {
                if ($name === 'foto' && $fileMatch[1] !== '') {
                    $this->putPhotoUpload = [
                        'name' => $fileMatch[1],
                        'content' => $value,
                    ];
                }
                continue;
            }

            $input[$name] = $value;
        }

        return $input;
    }

    private function storePhotoUpload(): ?string
    {
        if ($this->putPhotoUpload === null && !isset($_FILES['foto'])) {
            return null;
        }

        $isPutUpload = $this->putPhotoUpload !== null;
        if ($isPutUpload) {
            $file = $this->putPhotoUpload;
            $fileContent = $file['content'];
            $fileSize = strlen($fileContent);
        } else {
            $file = $_FILES['foto'];
            if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['name'], $file['size'])) {
                $this->respond(422, false, 'Data berkas foto tidak valid.', null);
            }
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                return null;
            }
            if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                $this->respond(422, false, 'Berkas foto gagal diunggah.', null);
            }
            $fileSize = (int) $file['size'];
            $fileContent = null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = [
            'jpg' => IMAGETYPE_JPEG,
            'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
        ];
        if ($fileSize > 2 * 1024 * 1024) {
            $this->respond(422, false, 'Ukuran berkas maksimal 2MB.', null);
        }
        $image = $isPutUpload
            ? @getimagesizefromstring($fileContent)
            : @getimagesize($file['tmp_name']);
        if (
            !isset($allowedTypes[$extension])
            || $image === false
            || $image[2] !== $allowedTypes[$extension]
        ) {
            $this->respond(422, false, 'Berkas harus berupa gambar JPG/PNG yang valid.', null);
        }

        $uploadDirectory = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
            $this->respond(500, false, 'Folder penyimpanan foto tidak tersedia.', null);
        }

        $filename = 'foto_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $uploadDirectory . '/' . $filename;
        if ($isPutUpload) {
            $stored = file_put_contents($destination, $fileContent, LOCK_EX) === $fileSize;
        } else {
            $stored = move_uploaded_file($file['tmp_name'], $destination);
        }
        if (!$stored) {
            if ($isPutUpload && is_file($destination)) {
                unlink($destination);
            }
            $this->respond(500, false, 'Foto gagal disimpan.', null);
        }

        return $filename;
    }

    private function removePhoto(?string $filename): void
    {
        if (
            $filename === null
            || preg_match('/\\Afoto_[a-f0-9]{32}\\.(?:jpg|jpeg|png)\\z/', $filename) !== 1
        ) {
            return;
        }

        $path = __DIR__ . '/../../public/uploads/' . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function queryId(): ?int
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function validateNimFormat(array $input): bool
    {
        $nim = $input['nim'] ?? null;
        if (!is_scalar($nim) || preg_match('/^[0-9]{9}$/', trim((string) $nim)) !== 1) {
            $message = 'Format NIM tidak valid, harus 9 digit.';
            Response::json(422, false, $message, null, ['nim' => [$message]]);
            return false;
        }

        return true;
    }

    private function respond(
        int $statusCode,
        bool $success,
        string $message,
        mixed $data,
        array $errors = []
    ): void {
        Response::json($statusCode, $success, $message, $data, $errors);
    }

    private function logException(Throwable $exception, string $context): void
    {
        error_log(sprintf(
            '%s [%s] %s: %s',
            date('Y-m-d H:i:s'),
            $context,
            get_class($exception),
            $exception->getMessage()
        ));
    }
}
