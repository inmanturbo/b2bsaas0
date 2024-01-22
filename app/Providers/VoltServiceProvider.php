<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inmanturbo\B2bSaas\MergesVoltMounts;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{

    use MergesVoltMounts;

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        $this->mergeVoltMounts([
            resource_path('views/livewire'),
            resource_path('views/pages'),
        ]);
    }
}
