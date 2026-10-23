<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class ImageUploadSecurity
{
    public static function rules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file', 'image', 'mimes:jpeg,png,jpg,gif',
            'extensions:jpeg,png,jpg,gif', 'max:2048',
            'dimensions:max_width=8192,max_height=8192',
        ];
    }

    public static function validate(UploadedFile $file): void
    {
        Validator::make(['image' => $file], ['image' => self::rules(true)])->validate();
    }

    public static function filename(UploadedFile $file): string
    {
        // Use the detected content type, never the browser-supplied extension.
        $extension = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            default => throw new \InvalidArgumentException('Unsupported image type.'),
        };

        return bin2hex(random_bytes(16)).'.'.$extension;
    }
}
