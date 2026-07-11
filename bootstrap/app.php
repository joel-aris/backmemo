<?php

declare(strict_types=1);

use App\Http\Middleware\AdminSubdomain;
use App\Http\Middleware\BruteForceProtection;
use App\Http\Middleware\RequireTwoFactorConfirmed;
use App\Http\Middleware\RotateSessionOnLogin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            SecurityHeaders::class,
            SetLocale::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'brute.force' => BruteForceProtection::class,
            '2fa.confirmed' => RequireTwoFactorConfirmed::class,
            'admin.subdomain' => AdminSubdomain::class,
            'session.rotate' => RotateSessionOnLogin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            return response()->json([
                'message' => 'Les donnees fournies sont invalides.',
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (\Exception $e, Request $request) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['message' => 'Ressource non trouvee.'], 404);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json(['message' => 'Non authentifie.'], 401);
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                return response()->json(['message' => 'Acces refuse.'], 403);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return response()->json(['message' => $e->getMessage() ?: 'Erreur HTTP.'], $e->getStatusCode());
            }

            Log::error($e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Erreur serveur.'], 500);
        });
    })
    ->create();
