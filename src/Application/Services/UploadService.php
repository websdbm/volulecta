<?php

declare(strict_types=1);

namespace App\Application\Services;

use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadService
{
    private string $uploadPath;

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = rtrim($uploadPath, DIRECTORY_SEPARATOR);
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }

    /**
     * @param UploadedFileInterface $uploadedFile
     * @param string $subDir
     * @return string The relative path to the uploaded file from public root
     */
    public function upload(UploadedFileInterface $uploadedFile, string $subDir = ''): string
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Failed to upload file.');
        }

        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%s', $basename, $extension);

        $targetDir = $this->uploadPath;
        if ($subDir) {
            $targetDir .= DIRECTORY_SEPARATOR . trim($subDir, DIRECTORY_SEPARATOR);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
        $uploadedFile->moveTo($targetPath);

        // Return relative path for web access
        $publicPos = strpos($targetPath, 'public');
        if ($publicPos !== false) {
            return str_replace(DIRECTORY_SEPARATOR, '/', substr($targetPath, $publicPos + 6));
        }

        return $filename;
    }
}
