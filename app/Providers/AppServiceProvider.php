<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // API da anamnese: limite por IP e por token (contrato, seção 4)
        RateLimiter::for('api-anamnese', function (Request $request) {
            $porMinuto = config('anamnese.api_limite_por_minuto');
            $token = hash('sha256', (string) $request->bearerToken());

            return [
                Limit::perMinute($porMinuto)->by('ip|'.$request->ip()),
                Limit::perMinute($porMinuto)->by('token|'.$token),
            ];
        });
    }
}
