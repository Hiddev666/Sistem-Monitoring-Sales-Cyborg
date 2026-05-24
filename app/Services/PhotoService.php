<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoService
{
    /**
     * Storage disk for photos
     */
    protected string $disk = 'local';
    
    /**
     * Base photo directory
     */
    protected string $photoDirectory = 'photos/visits';
    
    /**
     * Maximum file size in bytes (5MB)
     */
    protected int $maxFileSize = 5242880;
    
    /**
     * Allowed MIME types
     */
    protected array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/jpg'
    ];

    /**
     * Store a photo from check-in/check-out
     * 
     * @param UploadedFile $file
     * @param int $jadwalKlienId
     * @param string $type 'checkin' or 'checkout'
     * @param int $userId
     * @return array ['success' => bool, 'path' => string, 'message' => string]
     */
    public function storeVisitPhoto(UploadedFile $file, int $jadwalKlienId, string $type, int $userId): array
    {
        // Validate file
        $validation = $this->validatePhoto($file);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'path' => null,
                'message' => $validation['message']
            ];
        }

        try {
            // Create directory path: photos/visits/YYYY/MM/DD/{user_id}/{type}/
            $date = now();
            $directory = "{$this->photoDirectory}/{$date->year}/{$date->format('m')}/{$date->format('d')}/{$userId}/{$type}";
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = "jadwal_klien_{$jadwalKlienId}_" . Str::random(12) . ".{$extension}";
            
            // Store file
            $path = Storage::disk($this->disk)->putFileAs(
                $directory,
                $file,
                $filename
            );

            // Return success response
            return [
                'success' => true,
                'path' => $path,
                'url' => Storage::disk($this->disk)->url($path),
                'filename' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'message' => ucfirst($type) . ' photo saved successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Failed to save photo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Store a digital signature
     * 
     * @param string $base64Data Base64 encoded image data from canvas
     * @param int $jadwalKlienId
     * @param int $userId
     * @return array ['success' => bool, 'path' => string, 'message' => string]
     */
    public function storeSignature(string $base64Data, int $jadwalKlienId, int $userId): array
    {
        try {
            // Convert base64 to binary
            $imageData = substr($base64Data, strpos($base64Data, ',') + 1);
            $binaryData = base64_decode($imageData);

            // Validate size
            if (strlen($binaryData) > $this->maxFileSize) {
                return [
                    'success' => false,
                    'path' => null,
                    'message' => 'Signature data too large'
                ];
            }

            // Create directory path
            $date = now();
            $directory = "signatures/{$date->year}/{$date->format('m')}/{$date->format('d')}/{$userId}";
            
            // Generate filename
            $filename = "jadwal_klien_{$jadwalKlienId}_" . Str::random(12) . ".png";
            
            // Store signature
            $path = Storage::disk($this->disk)->put(
                "{$directory}/{$filename}",
                $binaryData
            );

            return [
                'success' => true,
                'path' => "{$directory}/{$filename}",
                'url' => Storage::disk($this->disk)->url("{$directory}/{$filename}"),
                'message' => 'Signature saved successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Failed to save signature: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a photo
     * 
     * @param string $path
     * @return bool
     */
    public function deletePhoto(string $path): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->delete($path);
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("Failed to delete photo: {$path}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get photo URL
     * 
     * @param string $path
     * @return string|null
     */
    public function getPhotoUrl(string $path): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->url($path);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get photo URL: {$path}", ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Validate photo file
     * 
     * @param UploadedFile $file
     * @return array ['valid' => bool, 'message' => string]
     */
    protected function validatePhoto(UploadedFile $file): array
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            return [
                'valid' => false,
                'message' => 'File size exceeds maximum of 5MB'
            ];
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            return [
                'valid' => false,
                'message' => 'Only JPG, PNG, and WebP images are allowed'
            ];
        }

        return [
            'valid' => true,
            'message' => 'File is valid'
        ];
    }

    /**
     * Get photo directory
     * 
     * @return string
     */
    public function getPhotoDirectory(): string
    {
        return $this->photoDirectory;
    }

    /**
     * Get all photos for a jadwal klien
     * 
     * @param int $jadwalKlienId
     * @return array
     */
    public function getPhotosForJadwalKlien(int $jadwalKlienId): array
    {
        $photos = [];
        
        try {
            $allFiles = Storage::disk($this->disk)->allFiles($this->photoDirectory);
            
            foreach ($allFiles as $file) {
                if (strpos($file, "jadwal_klien_{$jadwalKlienId}") !== false) {
                    $photos[] = [
                        'path' => $file,
                        'url' => Storage::disk($this->disk)->url($file),
                        'type' => strpos($file, '/checkin/') !== false ? 'checkin' : 'checkout'
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get photos for jadwal klien {$jadwalKlienId}", ['error' => $e->getMessage()]);
        }

        return $photos;
    }

    /**
     * Set custom storage disk
     * 
     * @param string $disk
     * @return self
     */
    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Set custom directory
     * 
     * @param string $directory
     * @return self
     */
    public function setDirectory(string $directory): self
    {
        $this->photoDirectory = $directory;
        return $this;
    }
}
