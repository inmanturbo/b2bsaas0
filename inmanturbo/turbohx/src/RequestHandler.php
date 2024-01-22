<?php

namespace Inmanturbo\TurboHX;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Laravel\Folio\Events\ViewMatched;
use Laravel\Folio\FolioManager;
use Laravel\Folio\Pipeline\MatchedView;
use Laravel\Folio\RequestHandler as FolioRequestHandler;
use Laravel\Folio\Router;
use Log;
use Symfony\Component\HttpFoundation\Response;

class RequestHandler extends FolioRequestHandler
{
    /**
     * Handle the incoming request using Folio.
     */
    public function __invoke(Request $request): mixed
    {
        foreach ($this->mountPaths as $mountPath) {
            $requestPath = '/' . ltrim($request->path(), '/');

            $uri = '/' . ltrim(substr($requestPath, strlen($mountPath->baseUri)), '/');

            if ($matchedView = app()->make(Router::class, ['mountPath' =>  $mountPath])->match($request, $uri)) {

                break;
            }
        }

        abort_unless($matchedView ?? null, 404);

        if ($name = $matchedView->name()) {
            $request->route()->action['as'] = $name;
        }

        app(Dispatcher::class)->dispatch(new ViewMatched($matchedView, $mountPath));

        $middleware = collect($this->middleware($mountPath, $matchedView));

        return (new Pipeline(app()))
            ->send($request)
            ->through($middleware->all())
            ->then(function (Request $request) use ($matchedView, $middleware) {
                if ($this->onViewMatch) {
                    ($this->onViewMatch)($matchedView);
                }

                $response = $this->renderUsing
                    ? ($this->renderUsing)($request, $matchedView)
                    : $this->toResponse($request, $matchedView);

                $app = app();

                $app->make(FolioManager::class)->terminateUsing(function () use ($middleware, $app, $request, $response) {
                    $middleware->filter(fn ($m) => is_string($m) && class_exists($m) && method_exists($m, 'terminate'))
                        ->map(fn (string $m) => $app->make($m))
                        ->each(fn (object $m) => $app->call([$m, 'terminate'], ['request' => $request, 'response' => $response]));

                    $request->route()->action['as'] = 'laravel-folio';
                });

                return $response;
            });
    }
}
