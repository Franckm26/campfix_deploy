<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorage
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->bucket = config('services.supabase.bucket', 'concerns');
    }

    /**
     * Upload file to Supabase Storage
     *
     * @param UploadedFile $file
     * @param string $path
     * @return string|null Public URL of uploaded file or null on failure
     */
    public function upload(UploadedFile $file, string $path): ?string
    {
        try {
            $filename = $path . '/' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => $file->getMimeType(),
            ])->withBody(
                file_get_contents($file->getRealPath()),
                $file->getMimeType()
            )->post("https://{$this->url}/storage/v1/object/{$this->bucket}/{$filename}");

            if ($response->successful()) {
                // Return public HTTPS URL
                return "https://{$this->url}/storage/v1/object/public/{$this->bucket}/{$filename}";
            }

            Log::error('Supabase upload failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase upload exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete file from Supabase Storage
     *
     * @param string $url
     * @return bool
     */
    public function delete(string $url): bool
    {
        try {
            // Extract filename from URL
            $filename = str_replace("https://{$this->url}/storage/v1/object/public/{$this->bucket}/", '', $url);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
            ])->delete("https://{$this->url}/storage/v1/object/{$this->bucket}/{$filename}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Supabase delete exception: ' . $e->getMessage());
            return false;
        }
    }
}
