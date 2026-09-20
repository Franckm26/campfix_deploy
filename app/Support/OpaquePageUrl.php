<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class OpaquePageUrl
{
    public function url(string $uri): string
    {
        return url('/hash/'.$this->encode($uri));
    }

    public function encode(string $uri): string
    {
        if (! $this->isSafeLocalUri($uri)) {
            throw new \InvalidArgumentException('Only local application paths can be made opaque.');
        }

        $encrypted = Crypt::encryptString($uri);

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    public function decode(string $token): ?string
    {
        if (! preg_match('/^[A-Za-z0-9_-]+$/', $token)) {
            return null;
        }

        $padding = (4 - strlen($token) % 4) % 4;
        $encoded = strtr($token.str_repeat('=', $padding), '-_', '+/');
        $encrypted = base64_decode($encoded, true);

        if ($encrypted === false) {
            return null;
        }

        try {
            $uri = Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }

        return $this->isSafeLocalUri($uri) ? $uri : null;
    }

    private function isSafeLocalUri(string $uri): bool
    {
        if ($uri === '' || strlen($uri) > 4096 || ! Str::startsWith($uri, '/')) {
            return false;
        }

        if (Str::startsWith($uri, ['//', '/hash/', '/api/']) || str_contains($uri, '\\')) {
            return false;
        }

        $parts = parse_url($uri);

        return $parts !== false
            && ! isset($parts['scheme'])
            && ! isset($parts['host'])
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }
}
