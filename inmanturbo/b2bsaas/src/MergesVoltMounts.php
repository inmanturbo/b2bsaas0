<?php

namespace Inmanturbo\B2bSaas;

use Livewire\Volt\Volt;

trait MergesVoltMounts
{
    protected function mergeVoltMounts($paths)
    {
        $voltPaths = collect(Volt::paths())->map(function ($path) {
            return $path->path;
        })->toArray();

        $paths = array_merge($voltPaths, $paths);

        Volt::mount($paths);
    }
}