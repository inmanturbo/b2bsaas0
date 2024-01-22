<?php

namespace Inmanturbo\TurboHX;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Laravel\Folio\MountPath;
use Laravel\Folio\Pipeline\MatchedView;

class TurboHX extends \Laravel\Folio\FolioManager
{
    /**
     * Registers the given route.
     *
     * @param  array<string, array<int, string>>  $middleware
     */
    public function registerRoute(string $path, string $uri, array $middleware, ?string $domain): void
    {
        $path = realpath($path);
        $uri = '/' . ltrim($uri, '/');

        if (! is_dir($path)) {
            throw new InvalidArgumentException("The given path [{$path}] is not a directory.");
        }

        $this->mountPaths[] = $mountPath = new MountPath(
            $path,
            $uri,
            $middleware,
            $domain,
        );

        $prefix = rtrim($mountPath->baseUri, '/');

        Route::any($prefix . '{any}', $this->handler())->where('any', '.*')->name('laravel-folio');
    }

    /**
     * Get the Folio request handler function.
     */
    protected function handler(): Closure
    {
        return function (Request $request) {
            $this->terminateUsing = null;

            $mountPaths = collect($this->mountPaths)->filter(
                fn (MountPath $mountPath) => str_starts_with(mb_strtolower('/' . $request->path()), $mountPath->baseUri)
            )->all();

            return (new RequestHandler(
                $mountPaths,
                $this->renderUsing,
                fn (MatchedView $matchedView) => $this->lastMatchedView = $matchedView,
            ))($request);
        };
    }
}
