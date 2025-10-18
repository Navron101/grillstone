<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Inventory\Contracts\InventoryEngine::class,
            \App\Services\Inventory\FifoInventoryEngine::class
        );
    }

    public function boot(): void {}
}
