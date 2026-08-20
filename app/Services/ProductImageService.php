<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final readonly class ProductImageService
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * @var array<string, string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private string $projectRoot,
        private string $baseUrl
    ) {
    }

    /**
     * @param array<string, mixed> $file
     */
    public function upload(array $file): string
    {
        $this->validateUpload($file);

        $temporaryPath = (string)$file['tmp_name'];

        $mimeType = $this->detectMimeType(
            $temporaryPath
        );

        $extension = self::ALLOWED_MIME_TYPES[
            $mimeType
        ] ?? null;

        if ($extension === null) {
            throw new RuntimeException(
                'Formato de imagen no permitido. Solo JPG, PNG y WEBP.'
            );
        }

        $uploadDirectory = $this->uploadDirectory();

        if (
            !is_dir($uploadDirectory) &&
            !mkdir(
                $uploadDirectory,
                0775,
                true
            ) &&
            !is_dir($uploadDirectory)
        ) {
            throw new RuntimeException(
                'No se pudo crear el directorio de imágenes.'
            );
        }

        $filename = $this->generateFilename(
            $extension
        );

        $destination =
            $uploadDirectory .
            DIRECTORY_SEPARATOR .
            $filename;

        if (
            !move_uploaded_file(
                $temporaryPath,
                $destination
            )
        ) {
            throw new RuntimeException(
                'No se pudo guardar la imagen.'
            );
        }

        return $this->publicImagePath(
            $filename
        );
    }

    public function delete(
        ?string $publicPath
    ): void {
        if (
            $publicPath === null ||
            trim($publicPath) === ''
        ) {
            return;
        }

        $urlPath = parse_url(
            $publicPath,
            PHP_URL_PATH
        );

        if (!is_string($urlPath)) {
            return;
        }

        $filename = basename($urlPath);

        if ($filename === '') {
            return;
        }

        $physicalPath =
            $this->uploadDirectory() .
            DIRECTORY_SEPARATOR .
            $filename;

        if (
            is_file($physicalPath) &&
            !unlink($physicalPath)
        ) {
            throw new RuntimeException(
                'No se pudo eliminar la imagen.'
            );
        }
    }

    /**
     * @param array<string, mixed> $file
     */
    public function hasNewUpload(
        array $file
    ): bool {
        return isset($file['error'])
            && (int)$file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function validateUpload(
        array $file
    ): void {
        if (
            !isset(
                $file['error'],
                $file['tmp_name'],
                $file['size']
            )
        ) {
            throw new RuntimeException(
                'La información de la imagen no es válida.'
            );
        }

        $error = (int)$file['error'];

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                $this->uploadErrorMessage($error)
            );
        }

        $size = (int)$file['size'];

        if ($size <= 0) {
            throw new RuntimeException(
                'La imagen está vacía.'
            );
        }

        if ($size > self::MAX_FILE_SIZE) {
            throw new RuntimeException(
                'La imagen supera el tamaño máximo de 5 MB.'
            );
        }

        $temporaryPath =
            (string)$file['tmp_name'];

        if (
            $temporaryPath === '' ||
            !is_uploaded_file($temporaryPath)
        ) {
            throw new RuntimeException(
                'El archivo recibido no es una subida válida.'
            );
        }
    }

    private function detectMimeType(
        string $path
    ): string {
        $finfo = new \finfo(
            FILEINFO_MIME_TYPE
        );

        $mimeType = $finfo->file($path);

        if (!is_string($mimeType)) {
            throw new RuntimeException(
                'No se pudo identificar el tipo de imagen.'
            );
        }

        return $mimeType;
    }

    private function generateFilename(
        string $extension
    ): string {
        return sprintf(
            'vino_%s.%s',
            bin2hex(random_bytes(16)),
            $extension
        );
    }

    private function uploadDirectory(): string
    {
        return
            rtrim(
                $this->projectRoot,
                DIRECTORY_SEPARATOR
            )
            . DIRECTORY_SEPARATOR
            . 'assets'
            . DIRECTORY_SEPARATOR
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'products';
    }

    private function publicImagePath(
        string $filename
    ): string {
        return sprintf(
            '%s/assets/uploads/products/%s',
            rtrim($this->baseUrl, '/'),
            rawurlencode($filename)
        );
    }

    private function uploadErrorMessage(
        int $error
    ): string {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                'La imagen supera el tamaño permitido.',

            UPLOAD_ERR_PARTIAL =>
                'La imagen se subió de forma incompleta.',

            UPLOAD_ERR_NO_FILE =>
                'Debe seleccionar una imagen.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'No existe un directorio temporal para la subida.',

            UPLOAD_ERR_CANT_WRITE =>
                'No se pudo escribir la imagen en el servidor.',

            UPLOAD_ERR_EXTENSION =>
                'Una extensión de PHP detuvo la subida.',

            default =>
                'Ocurrió un error al procesar la imagen.',
        };
    }
}