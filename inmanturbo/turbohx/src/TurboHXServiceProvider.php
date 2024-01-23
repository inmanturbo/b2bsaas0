<?php

namespace Inmanturbo\TurboHX;

use Illuminate\Support\Facades\File;
use Laravel\Folio\Folio;
use Laravel\Folio\FolioManager;
use Laravel\Folio\Router as FolioRouter;

class TurboHXServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FolioManager::class, TurboHX::class);
        $this->app->bind(FolioRouter::class, Router::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/turbohx.php',
            'turbohx'
        );
    }

    public function boot()
    {
        // foreach (File::glob(base_path('.www').'/[0-9]*', GLOB_ONLYDIR) as $version) {
        //     $version = basename($version);
        //     Folio::path(base_path('.www/'.$version))
        //         ->uri('/v'.$version)
        //         ->middleware([
        //             'web',
        //             'auth:sanctum',
        //             config('jetstream.auth_session'),
        //             'verified',
        //         ]);
        // }
    }
}
