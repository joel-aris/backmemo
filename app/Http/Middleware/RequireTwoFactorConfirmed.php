<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireTwoFactorConfirmed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->two_factor_confirmed_at) {
            return response()->json([
                'message' => 'Double authentification obligatoire.',
                'requires_2fa_setup' => true,
            ], 423);
        }

        return $next($request);
    }
}
