<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoInjectionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->isRedirect()) {
            return $response;
        }

        $path = '/' . ltrim($request->path(), '/');

        if ($path !== '/separation') {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || strpos($content, '<meta name="description"') !== false) {
            return $response;
        }

        $seoConfig = config('seo.pages./separation', []);
        $title = $seoConfig['title'] ?? config('seo.default.title');
        $description = $seoConfig['description'] ?? config('seo.default.description');
        $siteName = config('seo.site_name');
        $url = url()->current();

        $seoHtml = <<<HTML
<meta name="description" content="{$description}">
<meta name="robots" content="index,follow">
<link rel="canonical" href="{$url}">
<meta property="og:type" content="website">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="{$description}">
<meta property="og:url" content="{$url}">
<meta property="og:site_name" content="{$siteName}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$title}">
<meta name="twitter:description" content="{$description}">
<meta name="google-site-verification" content="2bjv5oSI_qog0xVzn6zLSJ9Xliyd-RnNH_nPuazdTGQ">
HTML;

        $newContent = str_replace('</title>', "</title>\n{$seoHtml}", $content);

        $oldTitle = '<title>Screen Print Separations Editor | MsterCV</title>';
        $newTitle = '<title>' . e($title) . '</title>';
        $newContent = str_replace($oldTitle, $newTitle, $newContent);

        $baseUrl = config('app.url');
        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $baseUrl,
            ],
            [
                '@type' => 'WebApplication',
                'name' => 'PDF Color Separation',
                'url' => $baseUrl . '/separation',
                'description' => 'Pisahkan warna PDF untuk kebutuhan desain dan printing.',
                'applicationCategory' => 'DesignApplication',
                'operatingSystem' => 'Web',
            ],
        ];

        $ldJson = '<script type="application/ld+json">' . json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
        $newContent = str_replace('</head>', "{$ldJson}\n</head>", $newContent);

        $seoContentHtml = view('partials.separation-seo-content')->render();
        $newContent = str_replace('</body>', "{$seoContentHtml}\n</body>", $newContent);

        $response->setContent($newContent);

        return $response;
    }
}
