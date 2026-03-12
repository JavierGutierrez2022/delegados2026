<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        \Illuminate\Support\Facades\Event::listen(Login::class, function ($event) {
            try {
                ActivityLog::create([
                    'user_id' => optional($event->user)->id,
                    'action' => 'LOGIN',
                    'description' => 'Inicio de sesion',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('No se pudo registrar auditoria de LOGIN', [
                    'user_id' => optional($event->user)->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        \Illuminate\Support\Facades\Event::listen(Logout::class, function ($event) {
            try {
                ActivityLog::create([
                    'user_id' => optional($event->user)->id,
                    'action' => 'LOGOUT',
                    'description' => 'Cierre de sesion',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('No se pudo registrar auditoria de LOGOUT', [
                    'user_id' => optional($event->user)->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}

