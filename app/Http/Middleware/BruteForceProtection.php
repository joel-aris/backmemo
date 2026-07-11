<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class BruteForceProtection
{
    public function __construct(private readonly RateLimiter $limiter)
    {
    }

    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 15): Response
    {
        $key = 'brute_force_' . $request->ip();

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Trop de tentatives. Veuillez réessayer plus tard.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 422) {
            $this->limiter->hit($key, $decayMinutes * 60);
        } elseif ($response->getStatusCode() === 200) {
            $this->limiter->clear($key);
        }

        return $response;
    }
}