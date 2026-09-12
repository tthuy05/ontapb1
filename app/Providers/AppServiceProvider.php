<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        RateLimiter::for('owner-login', function (Request $request): Limit {
            $login = Str::lower(Str::limit((string) $request->input('login'), 100, ''));

            return Limit::perMinute(5)->by($request->ip().'|'.$login);
        });

        RateLimiter::for('health', fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('study-write', fn (Request $request): Limit => Limit::perMinute(120)
            ->by($request->ip().'|'.$request->session()->getId()));

        if ((bool) config('app.force_https')) {
            URL::forceScheme('https');
        }
    }
}
