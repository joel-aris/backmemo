<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

final class GoogleOAuthController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function redirect(): JsonResponse
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentification Google echouee.'], 401);
        }

        $user = User::query()
            ->where('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Aucun compte VALIDIKA associe a cet email Google.'], 404);
        }

        if ($user->two_factor_secret && ! $request->filled('otp')) {
            return response()->json(['message' => 'Code OTP requis pour ce compte.', 'requires_2fa' => true, 'user_id' => $user->id], 423);
        }

        if ($request->filled('otp')) {
            $secret = decrypt((string) $user->two_factor_secret);
            $valid = app('pragmarx.google2fa')->verifyKey($secret, $request->string('otp')->toString());

            abort_if(! $valid, 422, 'Code OTP invalide.');
        }

        if (! $user->two_factor_confirmed_at) {
            $user->forceFill(['two_factor_confirmed_at' => now('UTC')])->save();
        }

        $user->tokens()->where('name', 'api')->delete();
        $token = $user->createToken('api', ['*'], now('UTC')->addHours(12))->plainTextToken;

        $this->auditLogService->record($request, 'auth.google_login', $user);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'requires_email_verification' => ! $user->hasVerifiedEmail(),
            'requires_2fa_setup' => ! $user->two_factor_confirmed_at,
            'user' => $user,
        ]);
    }
}
