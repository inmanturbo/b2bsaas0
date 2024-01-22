<?php

namespace Inmanturbo\B2bSaas;

use Livewire\Volt\Volt;

trait MergesVoltMounts
{
    /**
     * Merge the given paths with the existing Volt mounts.
     */
    protected function mergeVoltMounts(array $paths): void
    {
        $voltPaths = collect(Volt::paths())->map(function ($path) {
            return $path->path;
        })->toArray();

        $paths = array_merge($voltPaths, $paths);

        Volt::mount($paths);
    }
}
