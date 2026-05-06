<?php

namespace App\Providers;

use App\Models\Protocol;
use App\Models\Thread;
use App\Observers\ProtocolObserver;
use App\Observers\ThreadObserver;
use App\Services\TypesenseService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TypesenseService::class, function () {
            return new TypesenseService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Protocol::observe(ProtocolObserver::class);
        Thread::observe(ThreadObserver::class);
    }
}
