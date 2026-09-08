<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        if ($this->app->environment('production') && !empty(config('app.url'))) {
            URL::forceRootUrl(config('app.url'));
        }

        $this->bootRateLimiters();
        $this->bootViewComposers();
    }

    private function bootRateLimiters(): void
    {
        RateLimiter::for('register', function () {
            return Limit::perMinute(5);
        });

        RateLimiter::for('convert', function (Request $request) {
            $user = $request->user();
            if ($user) {
                return Limit::perMinute(30)->by('convert-user-' . $user->id);
            }
            return Limit::perMinute(5)->by($request->ip());
        });
    }

    private function bootViewComposers(): void
    {
        View::composer('*', function ($view) {
            $path = '/' . ltrim(request()->path(), '/');
            $seoPages = config('seo.pages', []);
            $seoConfig = $seoPages[$path] ?? [];
            $isPrivate = in_array($path, config('seo.private_paths', []));

            $seo = [
                'title' => $seoConfig['title'] ?? config('seo.default.title'),
                'description' => $seoConfig['description'] ?? config('seo.default.description'),
                'type' => 'website',
                'url' => url()->current(),
                'noindex' => $isPrivate,
                'h1' => $seoConfig['h1'] ?? null,
            ];

            $seoLd = $this->buildStructuredData($path);

            $view->with('seo', $seo)->with('seoLd', $seoLd);
        });
    }

    private function buildStructuredData(string $path): ?array
    {
        $baseUrl = config('app.url');
        $siteName = config('seo.site_name');

        $webSite = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $baseUrl,
        ];

        $tools = [
            '/' => [
                '@type' => 'WebApplication',
                'name' => 'PDF Converter',
                'url' => $baseUrl . '/',
                'description' => 'Convert PDF ke PNG atau JPG secara online.',
                'applicationCategory' => 'MultimediaApplication',
                'operatingSystem' => 'Web',
            ],
            '/separation' => [
                '@type' => 'WebApplication',
                'name' => 'PDF Color Separation',
                'url' => $baseUrl . '/separation',
                'description' => 'Pisahkan warna PDF untuk kebutuhan desain dan printing.',
                'applicationCategory' => 'DesignApplication',
                'operatingSystem' => 'Web',
            ],
            '/upscaler' => [
                '@type' => 'WebApplication',
                'name' => 'Image Upscaler',
                'url' => $baseUrl . '/upscaler',
                'description' => 'Upscale dan perbesar gambar hingga 4×.',
                'applicationCategory' => 'DesignApplication',
                'operatingSystem' => 'Web',
            ],
        ];

        if (isset($tools[$path])) {
            return [$webSite, $tools[$path]];
        }

        return null;
    }
}
