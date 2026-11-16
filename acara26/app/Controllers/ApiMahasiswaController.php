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

    private function storePhotoUpload(): ?string
    {
        if (!isset($_FILES['foto'])) {
            return null;
        }

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
        if ((int) $file['size'] > 2 * 1024 * 1024) {
            $this->respond(422, false, 'Ukuran berkas maksimal 2MB.', null);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = [
            'jpg' => IMAGETYPE_JPEG,
            'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
        ];
        $image = @getimagesize($file['tmp_name']);
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
        if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . '/' . $filename)) {
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
