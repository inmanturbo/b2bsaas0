<?php

namespace Inmanturbo\TurboHX\Pipeline;

use Closure;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\Pipeline\State;

class MatchRootIndex
{
    /**
     * Invoke the routing pipeline handler.
     */
    public function __invoke(State $state, Closure $next): mixed
    {
        if (trim($state->uri) === '/') {
            return file_exists($path = $state->mountPath.'/index.blade.php')
                    ? new MatchedView($path, $state->data)
                    : $next($state);
        }

        return $next($state);
    }
}
