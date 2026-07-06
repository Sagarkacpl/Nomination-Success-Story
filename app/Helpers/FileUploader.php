<?php

namespace App\Helpers;

use Exception;

/**
 * FileUploader
 * Usage:
 *   $uploader = new FileUploader('experience_letters'); // relative folder name under /uploads/
 *   $filename = $uploader->upload($_FILES['experience_letter']);
 *
 *   $uploader = new FileUploader('ca_profiles/documents'); // nested folder also works
 *   $filename = $uploader->upload($_FILES['some_doc'], ['application/pdf'], ['pdf'], 5*1024*1024);
 */
class FileUploader
{
    private string $destination;

    // Defaults (used if caller doesn't override per-upload)
    private array $defaultAllowedMime;
    private array $defaultAllowedExt;
    private int $defaultMaxSize;

    /**
     * @param string $relativePath Folder name (or nested path) INSIDE /uploads/, e.g. 'profile_pics', 'experience_letters', 'ca_profiles/docs'
     */
    public function __construct(string $relativePath)
    {
        // UPLOADS_BASE_DIR should be defined in config.php as:
        // define('UPLOADS_BASE_DIR', dirname(__DIR__, 2) . '/uploads/');
        $base = rtrim(UPLOADS_BASE_DIR, '/');
        $relativePath = trim($relativePath, '/');

        $this->destination = $base . '/' . $relativePath . '/';

        // Auto-create folder (and any missing parent folders) if it doesn't exist
        if (!is_dir($this->destination)) {
            if (!mkdir($this->destination, 0755, true) && !is_dir($this->destination)) {
                throw new Exception('Failed to create upload directory: ' . $this->destination);
            }
        }

        // Fallback defaults — images. Override per-call if uploading other file types.
        $this->defaultAllowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $this->defaultAllowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $this->defaultMaxSize = 2 * 1024 * 1024; // 2 MB
    }

    /**
     * @param array $file A single entry from $_FILES
     * @param array|null $allowedMime Override allowed MIME types for this upload (e.g. ['application/pdf'])
     * @param array|null $allowedExt  Override allowed extensions (e.g. ['pdf'])
     * @param int|null $maxSize       Override max size in bytes
     * @return string The generated filename (store this in DB)
     * @throws Exception on any validation failure
     */
    public function upload(
        array $file,
        ?array $allowedMime = null,
        ?array $allowedExt = null,
        ?int $maxSize = null
    ): string {
        $allowedMime = $allowedMime ?? $this->defaultAllowedMime;
        $allowedExt = $allowedExt ?? $this->defaultAllowedExt;
        $maxSize = $maxSize ?? $this->defaultMaxSize;

        $this->assertNoUploadError($file);
        $this->assertSizeWithinLimit($file, $maxSize);
        $extension = $this->assertExtensionAllowed($file, $allowedExt);
        $mime = $this->assertMimeAllowed($file, $allowedMime);
        $this->assertFileIsGenuine($file, $mime);

        $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = rtrim($this->destination, '/') . '/' . $newFilename;

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Possible file upload attack detected.');
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to move uploaded file.');
        }

        chmod($targetPath, 0644);

        return $newFilename;
    }

    /** Returns the destination folder path (useful if controller wants to build a URL/log path) */
    public function getDestination(): string
    {
        return $this->destination;
    }

    private function assertNoUploadError(array $file): void
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception('No file was uploaded.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed with error code: ' . $file['error']);
        }
    }

    private function assertSizeWithinLimit(array $file, int $maxSize): void
    {
        if ($file['size'] > $maxSize) {
            $maxMb = round($maxSize / 1024 / 1024, 1);
            throw new Exception("File is too large. Maximum allowed size is {$maxMb} MB.");
        }
    }

    private function assertExtensionAllowed(array $file, array $allowedExt): string
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExt, true)) {
            throw new Exception('File type not allowed. Allowed: ' . implode(', ', $allowedExt));
        }
        return $extension;
    }

    private function assertMimeAllowed(array $file, array $allowedMime): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMime, true)) {
            throw new Exception('Invalid file content type detected: ' . $mime);
        }
        return $mime;
    }

    /**
     * Extra guard against polyglot/spoofed files:
     * - images: must be decodable via getimagesize()
     * - pdf: must start with %PDF- magic bytes
     * - doc/docx: must match ZIP (docx) or OLE (doc) magic bytes
     */
    private function assertFileIsGenuine(array $file, string $mime): void
    {
        if (str_starts_with($mime, 'image/')) {
            if (@getimagesize($file['tmp_name']) === false) {
                throw new Exception('Uploaded file is not a valid image.');
            }
            return;
        }

        if ($mime === 'application/pdf') {
            $handle = fopen($file['tmp_name'], 'rb');
            $header = fread($handle, 5);
            fclose($handle);
            if ($header !== '%PDF-') {
                throw new Exception('Uploaded file is not a valid PDF.');
            }
            return;
        }

        // docx / xlsx / pptx are ZIP-based -> starts with "PK"
        if (in_array($mime, [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ], true)) {
            $handle = fopen($file['tmp_name'], 'rb');
            $header = fread($handle, 2);
            fclose($handle);
            if ($header !== 'PK') {
                throw new Exception('Uploaded file is not a valid document.');
            }
            return;
        }

        // Legacy .doc is OLE Compound File -> starts with D0 CF 11 E0
        if ($mime === 'application/msword') {
            $handle = fopen($file['tmp_name'], 'rb');
            $header = bin2hex(fread($handle, 4));
            fclose($handle);
            if ($header !== 'd0cf11e0') {
                throw new Exception('Uploaded file is not a valid document.');
            }
            return;
        }
    }
}