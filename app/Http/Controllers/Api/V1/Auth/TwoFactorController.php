<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

final class TwoFactorController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function enable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $secret = app('pragmarx.google2fa')->generateSecretKey();
        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::upper(Str::random(10) . '-' . Str::random(10)))->all();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => null,
        ])->save();

        $qr = app('pragmarx.google2fa')->getQRCodeUrl(config('app.name'), $user->email, $secret);
        $this->auditLogService->record($request, 'auth.2fa_enable_started', $user);

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qr,
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);
        /** @var User $user */
        $user = $request->user();
        $secret = decrypt((string) $user->two_factor_secret);
        $valid = app('pragmarx.google2fa')->verifyKey($secret, $request->string('otp')->toString());

        abort_if(! $valid, 422, 'Code OTP invalide.');

        $user->forceFill(['two_factor_confirmed_at' => now('UTC')])->save();
        $this->auditLogService->record($request, 'auth.2fa_verified', $user);

        return response()->json(['message' => 'Double authentification activee.']);
    }

    public function disable(Request $request): JsonResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);
        $request->user()?->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $this->auditLogService->record($request, 'auth.2fa_disabled', $request->user());

        return response()->json(['message' => 'Double authentification desactivee.']);
    }
}
