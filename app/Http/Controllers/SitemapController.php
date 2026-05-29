<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $baseUrl = config('app.url');
        
        // Define your public URLs here
        $urls = [
            [
                'loc' => $baseUrl,
                'changefreq' => 'daily',
                'priority' => '1.0',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => $baseUrl . '/login',
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => $baseUrl . '/register',
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'lastmod' => now()->toAtomString(),
            ],
        ];

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $sitemap .= '  <url>' . "\n";
            $sitemap .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $sitemap .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $sitemap .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $sitemap .= '  </url>' . "\n";
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $baseUrl = config('app.url');
        
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /dashboard\n";
        $robots .= "Disallow: /my-concerns\n";
        $robots .= "Disallow: /my-events\n";
        $robots .= "Disallow: /settings\n";
        $robots .= "Disallow: /profile\n";
        $robots .= "\n";
        $robots .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return response($robots, 200)
            ->header('Content-Type', 'text/plain');
    }
}
