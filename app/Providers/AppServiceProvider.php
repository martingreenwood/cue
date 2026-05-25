<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Infrastructure\Ticketing\Spektrix\SpektrixTicketingProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TicketingProvider::class, SpektrixTicketingProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
