<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('terms.accept*') || $request->routeIs('logout')) {
            return $next($request);
        }

        if ($user->terms_accepted_version === (string) config('terms.current_version')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Terms of Use acceptance is required.');
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()->route('terms.accept');
    }
}
