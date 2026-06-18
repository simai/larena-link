<?php

declare(strict_types=1);

namespace Larena\Link\Providers;

use Illuminate\Support\ServiceProvider;

final class LinkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'larena-link');

        if (!$this->shouldLoadInternalRoutes()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../../routes/internal.php');
    }

    private function shouldLoadInternalRoutes(): bool
    {
        return $this->app->environment(['local', 'testing']);
    }
}
