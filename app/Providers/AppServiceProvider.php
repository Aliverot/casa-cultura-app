<?php

namespace App\Providers;

use App\Models\Activo;
use App\Models\Mantenimiento;
use App\Observers\AsistenteNotificacionesActivoObserver;
use App\Observers\AsistenteNotificacionesMantenimientoObserver;
use App\Services\AlertasOperativasService;
use App\Services\AsistenteNotificacionesDiario;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AlertasOperativasService::class);
        $this->app->singleton(AsistenteNotificacionesDiario::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        Activo::observe(AsistenteNotificacionesActivoObserver::class);
        Mantenimiento::observe(AsistenteNotificacionesMantenimientoObserver::class);
    }
}
