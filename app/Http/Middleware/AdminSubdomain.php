<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0] ?? '';

        if ($subdomain !== 'admin' && !str_starts_with($host, 'admin.')) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        return $next($request);
    }
}
