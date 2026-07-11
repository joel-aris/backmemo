<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    private const SUPPORTED = ['fr', 'en', 'ln', 'ktn', 'kg', 'sw'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');

        if ($locale) {
            $locale = strtolower(explode(',', (string) $locale)[0]);
            $locale = in_array($locale, self::SUPPORTED, true) ? $locale : 'fr';
        } else {
            $locale = $request->query('lang', 'fr');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
