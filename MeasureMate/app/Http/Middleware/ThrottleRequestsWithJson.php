<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequestsWithJson
{
    public function handle(Request $request, Closure $next, string $name = 'default', int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $key = $request->ip().':'.$name.':'.$request->route()?->getName();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please wait a moment and try again.',
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
