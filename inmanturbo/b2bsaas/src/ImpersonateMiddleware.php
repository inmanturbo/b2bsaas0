<?php

namespace Inmanturbo\B2bSaas;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImpersonateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (! Auth::check()) {
            return $next($request);
        }

        if ($request->has('start_impersonate')) {

            if (session()->has('impersonate')) {
                return $next($request);
            }

            $impersonatedUser = User::find($request->start_impersonate);

            if (! $impersonatedUser) {
                return $next($request);
            }

            if (! Gate::allows('impersonate', $impersonatedUser)) {
                return $next($request);
            }

            session()->put('impersonator', $impersonator = Auth::user()->id);
            session()->put('impersonate', $impersonatedUser->id);

            if (! $impersonatedUser) {
                return $next($request);
            }

            if (! Gate::allows('impersonate', $impersonatedUser)) {
                return $next($request);
            }

            Auth::guard('web')->login($impersonatedUser);
            Auth::guard('sanctum')->setUser($impersonatedUser);

            session()->now('flash.banner', 'You are now impersonating '.$impersonatedUser->name.'.');
            session()->now('flash.bannerStyle', 'success');

            return $next($request);
        }

        if ($request->has('stop_impersonate')) {

            if (! session()->has('impersonate') || ! session()->has('impersonator')) {
                return $next($request);
            }

            [$impersonatedUser, $impersonator] = $this->getImpersonation();

            session()->forget('impersonate');
            session()->forget('impersonator');

            Auth::guard('web')->login($impersonator);
            Auth::guard('sanctum')->setUser($impersonator);

            session()->now('flash.banner', 'You are no longer impersonating '.$impersonatedUser->name.'.');
            session()->now('flash.bannerStyle', 'success');
        }

        if (session()->has('impersonate')) {
            //
        }

        return $next($request);
    }

    protected function getImpersonation(): array
    {
        return [User::find(session('impersonate')), User::find(session('impersonator'))];
    }
}
