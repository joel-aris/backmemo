<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Notifications\EmailVerificationCode;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')->toString()),
        ]);
        $user->assignRole('Visiteur');
        $this->sendEmailVerificationCode($user);
        $this->auditLogService->record($request, 'auth.register', $user);

        return response()->json([
            'message' => 'Compte cree. Un code de verification email a ete envoye.',
            'user' => $user,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            $this->auditLogService->record($request, 'auth.login_failed', null, ['email' => $request->input('email')]);
            throw ValidationException::withMessages(['email' => ['Identifiants invalides.']]);
        }

        /** @var User $user */
        $user = Auth::user();

        $skipTwoFactorInLocal = app()->environment('local');

        if (! $skipTwoFactorInLocal && ! $user->two_factor_confirmed_at) {
            return response()->json([
                'message' => 'Configuration 2FA requise avant connexion.',
                'requires_2fa_setup' => true,
                'user' => $this->userPayload($user),
            ], 423);
        }

        if (! $skipTwoFactorInLocal && ! $request->filled('otp')) {
            return response()->json(['message' => 'Code OTP requis.', 'requires_2fa' => true], 423);
        }

        if (! $skipTwoFactorInLocal) {
            $valid = app('pragmarx.google2fa')->verifyKey(
                decrypt((string) $user->two_factor_secret),
                $request->string('otp')->toString()
            );

            abort_if(! $valid, 422, 'Code OTP invalide.');
        }

        $user->tokens()->where('name', 'api')->delete();
        $token = $user->createToken('api', ['*'], now('UTC')->addHours(12))->plainTextToken;
        $this->auditLogService->record($request, 'auth.login', $user);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'requires_email_verification' => ! $user->hasVerifiedEmail(),
            'requires_2fa_setup' => false,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        $this->auditLogService->record($request, 'auth.logout', $request->user());

        return response()->json(['message' => 'Session revoquee.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()?->load('roles', 'permissions')]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Si cet email existe, un lien de reinitialisation sera envoye.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Flux de reinitialisation pret pour integration notification.']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifie.'], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Adresse email deja verifiee.']);
        }

        $verificationHash = $user->email_verification_code ?? '';
        if (! Hash::check($data['code'], $verificationHash)) {
            return response()->json(['message' => 'Code de verification invalide.'], 422);
        }

        $user->email_verified_at = now('UTC');
        $user->email_verification_code = null;
        $user->save();

        return response()->json(['message' => 'Adresse email verifiee.']);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifie.'], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Adresse email deja verifiee.']);
        }

        $this->sendEmailVerificationCode($user);

        return response()->json(['message' => 'Code de verification email renvoye.']);
    }

    private function sendEmailVerificationCode(User $user): void
    {
        $verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->email_verification_code = Hash::make($verificationCode);
        $user->save();
        $user->notify(new EmailVerificationCode($verificationCode));
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->all(),
        ];
    }
}
