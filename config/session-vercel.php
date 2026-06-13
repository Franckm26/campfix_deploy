<?php

// Force database sessions on Vercel production
if (env('APP_ENV') === 'production' && isset($_ENV['VERCEL'])) {
    return [
        'driver' => 'database',
        'connection' => 'pgsql',
        'table' => 'sessions',
    ];
}

return [];
