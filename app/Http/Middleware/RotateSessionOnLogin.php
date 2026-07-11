<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class RotateSessionOnLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $request->isMethod('post') && $request->is('auth/login')) {
            $user->tokens()->where('name', 'api')->delete();
        }

        return $next($request);
    }
}
