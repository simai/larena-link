<?php

declare(strict_types=1);

namespace Larena\Link\Providers;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;

final class LinkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/larena-link.php', 'larena-link');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'larena-link');

        if ($this->shouldLoadInternalRoutes()) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/internal.php');
        }

        if ($this->shouldLoadPublicRoutes()) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/public.php');
        }
    }

    private function shouldLoadInternalRoutes(): bool
    {
        return $this->app->environment(['local', 'testing']);
    }

    private function shouldLoadPublicRoutes(): bool
    {
        if (!$this->app->environment(['local', 'testing'])) {
            return false;
        }

        /** @var ConfigRepository $config */
        $config = $this->app->make(ConfigRepository::class);

        return (bool) $config->get('larena-link.public_routes.enabled', false);
    }
}
