<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attempt Sanctum authentication if credentials are present, but never reject the request.
 *
 * This enables public GET endpoints to include user-specific fields (e.g. current_user_vote)
 * when the client sends a valid Bearer token.
 */
class OptionalSanctumAuth
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}

