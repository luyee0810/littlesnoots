<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turns a suspended account out of the site on its next request.
 *
 * Runs on every web request rather than on a route group: a suspension has to
 * take effect everywhere at once, and an account suspended mid-session
 * shouldn't keep working until they happen to log out.
 */
class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isSuspended()) {
            $reason = $user->suspension_reason;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', trim(
                'Your account has been suspended. '.($reason ? "Reason: {$reason}" : '')
            ));
        }

        return $next($request);
    }
}
