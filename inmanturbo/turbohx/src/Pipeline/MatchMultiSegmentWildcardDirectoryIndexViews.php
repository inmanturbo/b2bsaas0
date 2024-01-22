<?php

namespace Inmanturbo\TurboHX\Pipeline;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laravel\Folio\Pipeline\FindsWildcardViews;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\State;

class MatchMultiSegmentWildcardDirectoryIndexViews
{
    use FindsWildcardViews;

    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        if (! ($filesystem = (new Filesystem()))->exists($state->currentUriSegmentDirectory())) {
            return $next($state);
        }

        if ($path = $this->findWildcardMultiSegmentView($state->currentUriSegmentDirectory())) {
            return new MatchedView($state->currentUriSegmentDirectory().'/'.$path, $state->withData(
                Str::of($path)
                    ->before('.blade.php')
                    ->match('/\[\.\.\.(.*)\]/')->value(),
                array_slice(
                    $state->segments,
                    $state->currentIndex + 1,
                    $state->uriSegmentCount()
                )
            )->data);

        }

        return $next($state);
    }
}
