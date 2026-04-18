<?php

namespace App\Services;

use App\Services\SecurityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * OWASP A1: Injection - Secure File Upload Service
 * Provides secure file upload handling with comprehensive validation
 */
class SecureFileUpload
{
    /**
     * Allowed MIME types for different file categories
     */
    protected array $allowedMimeTypes = [
        'images' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
        'documents' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ],
        'reports' => [
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
        'concerns' => [
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
    ];

    /**
     * Maximum file sizes in bytes
     */
    protected array $maxFileSizes = [
        'images' => 2048 * 1024, // 2MB
        'documents' => 5120 * 1024, // 5MB
        'reports' => 2048 * 1024, // 2MB
        'concerns' => 2048 * 1024, // 2MB
    ];

    /**
     * Dangerous file extensions to block
     */
    protected array $dangerousExtensions = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
        'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp',
        'cgi', 'pl', 'py', 'sh', 'dll', 'so', 'dylib',
    ];

    /**
     * Validate and store uploaded file securely
     *
     * @param UploadedFile $file
     * @param string $category (images, documents, reports, concerns)
     * @param string $directory
     * @return string|null Path to stored file or null if validation failed
     */
    public function validateAndStore(UploadedFile $file, string $category, string $directory): ?string
    {
        try {
            // 1. Check if file is valid
            if (!$file->isValid()) {
                SecurityLogger::logFileUploadSecurity([
                    'filename' => $file->getClientOriginalName(),
                    'reason' => 'Invalid file upload',
                ]);
                return null;
            }

            // 2. Validate file size
            if (!$this->validateFileSize($file, $category)) {
                SecurityLogger::logFileUploadSecurity([
                    'filename' => $file->getClientOriginalName(),
                    'filesize' => $file->getSize(),
                    'reason' => 'File size exceeds limit',
                ]);
                return null;
            }

            // 3. Validate MIME type
            if (!$this->validateMimeType($file, $category)) {
                SecurityLogger::logFileUploadSecurity([
                    'filename' => $file->getClientOriginalName(),
                    'mimetype' => $file->getMimeType(),
                    'reason' => 'Invalid MIME type',
                ]);
                return null;
            }

            // 4. Check for dangerous file extensions
            if ($this->hasDangerousExtension($file)) {
                SecurityLogger::logFileUploadSecurity([
                    'filename' => $file->getClientOriginalName(),
                    'reason' => 'Dangerous file extension detected',
                ]);
                return null;
            }

            // 5. Generate secure filename
            $secureFilename = $this->generateSecureFilename($file);

            // 6. Store file
            $path = $file->storeAs($directory, $secureFilename, 'public');

            // 7. Verify file was stored correctly
            if (!Storage::disk('public')->exists($path)) {
                SecurityLogger::logFileUploadSecurity([
                    'filename' => $secureFilename,
                    'reason' => 'File storage failed',
                ]);
                return null;
            }

            return $path;

        } catch (\Exception $e) {
            SecurityLogger::logFileUploadSecurity([
                'filename' => $file->getClientOriginalName(),
                'reason' => 'Exception during file upload: ' . $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Validate file size
     */
    protected function validateFileSize(UploadedFile $file, string $category): bool
    {
        $maxSize = $this->maxFileSizes[$category] ?? $this->maxFileSizes['images'];
        return $file->getSize() <= $maxSize;
    }

    /**
     * Validate MIME type
     */
    protected function validateMimeType(UploadedFile $file, string $category): bool
    {
        $allowedTypes = $this->allowedMimeTypes[$category] ?? $this->allowedMimeTypes['images'];
        return in_array($file->getMimeType(), $allowedTypes);
    }

    /**
     * Check for dangerous file extensions
     */
    protected function hasDangerousExtension(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, $this->dangerousExtensions);
    }

    /**
     * Generate secure filename — fully random, no guessable basename
     */
    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    /**
     * Delete file securely
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            return true; // File doesn't exist, consider it "deleted"
        } catch (\Exception $e) {
            SecurityLogger::logFileUploadSecurity([
                'filename' => $path,
                'reason' => 'Exception during file deletion: ' . $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get file validation rules for form request validation
     */
    public function getValidationRules(string $category): array
    {
        $maxSize = $this->maxFileSizes[$category] ?? $this->maxFileSizes['images'];
        $maxSizeKB = intval($maxSize / 1024);

        $allowedTypes = $this->allowedMimeTypes[$category] ?? $this->allowedMimeTypes['images'];

        return [
            'required',
            'file',
            'max:' . $maxSizeKB,
            function ($attribute, $value, $fail) use ($allowedTypes) {
                if ($value && !in_array($value->getMimeType(), $allowedTypes)) {
                    $fail('The ' . $attribute . ' must be a valid file type.');
                }
            },
        ];
    }
}