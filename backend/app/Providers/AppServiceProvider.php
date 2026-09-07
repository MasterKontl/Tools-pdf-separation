<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        if ($this->app->environment('production') && !empty(config('app.url'))) {
            URL::forceRootUrl(config('app.url'));
        }

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
}
