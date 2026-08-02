<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsProvider
{
    /**
     * Guards the provider dashboard. A user without a profile is sent to onboarding
     * rather than shown a 403 — they haven't done anything wrong, they just haven't
     * signed up as a provider yet.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasProviderProfile()) {
            return redirect()
                ->route('provider.onboarding')
                ->with('error', 'Set up your provider profile first.');
        }

        return $next($request);
    }
}
