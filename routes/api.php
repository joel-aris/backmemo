<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\GoogleOAuthController;
use App\Http\Controllers\Api\V1\Auth\TwoFactorController;
use App\Http\Controllers\Api\V1\CandidacyController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\OcrController;
use App\Http\Controllers\Api\V1\PharmacistController;
use App\Http\Controllers\Api\V1\PublicVerificationController;
use App\Http\Controllers\Api\V1\SearchHistoryController;
use App\Http\Controllers\Api\V1\TerritoryController;
use App\Http\Controllers\Api\V1\UserQuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/verify/{qrCode}', PublicVerificationController::class)
        ->where('qrCode', '[A-Za-z0-9\-_:.]+')
        ->name('api.v1.verify.public');

    Route::get('/territories/provinces', [TerritoryController::class, 'provinces'])->name('api.v1.territories.provinces');
    Route::get('/territories/cities', [TerritoryController::class, 'cities'])->name('api.v1.territories.cities');
    Route::get('/territories/communes', [TerritoryController::class, 'communes'])->name('api.v1.territories.communes');

    Route::get('/lang', function () {
        return response()->json(['locale' => app()->getLocale(), 'supported' => ['fr', 'en', 'ln', 'ktn', 'kg', 'sw']]);
    });

    Route::post('/lang', function (Request $request) {
        $request->validate(['locale' => ['required', 'string', 'in:fr,en,ln,ktn,kg,sw']]);
        app()->setLocale($request->string('locale'));
        session(['locale' => $request->string('locale')]);

        return response()->json(['locale' => app()->getLocale()]);
    });

    Route::post('/contact', [ContactController::class, 'message'])->name('api.v1.contact.message');
    Route::post('/candidacies', [CandidacyController::class, 'store'])->middleware('throttle:5,1')->name('api.v1.candidacies.public.store');

    Route::get('/faqs/categories', [FaqController::class, 'categories'])->name('api.v1.faqs.categories');
    Route::get('/faqs', [FaqController::class, 'index'])->name('api.v1.faqs.index');
    Route::post('/questions', [UserQuestionController::class, 'store'])->name('api.v1.questions.store');
    Route::apiResource('pharmacists', PharmacistController::class)
        ->only(['index', 'show']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('brute.force');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:auth');
        Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->middleware('auth:sanctum');
        Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->middleware('auth:sanctum');
        Route::get('/google/redirect', [GoogleOAuthController::class, 'redirect'])->name('api.v1.auth.google.redirect');
        Route::post('/google/callback', [GoogleOAuthController::class, 'callback'])->name('api.v1.auth.google.callback');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
            Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
            Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);
            Route::get('/search-history', [SearchHistoryController::class, 'index']);
            Route::post('/search-history', [SearchHistoryController::class, 'store']);
            Route::get('/candidacies', [CandidacyController::class, 'mine'])->name('api.v1.auth.candidacies.mine');
        });
    });

    // Public: OCR pre-fill is used from the public, unauthenticated candidacy
    // form (scan a professional card/diploma/ID to prefill name/order number/
    // expiry before an account even exists). Rate-limited instead of gated
    // behind auth:sanctum, which made it unusable from that exact flow.
    Route::middleware('throttle:10,1')->post('/ocr/extract', [OcrController::class, 'extract'])
        ->name('api.v1.ocr.extract');

    Route::middleware(['auth:sanctum', 'verified', 'role:Administrateur|Super Admin'])->prefix('admin')->group(function (): void {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        Route::get('/questions', [UserQuestionController::class, 'index']);
        Route::get('/questions/{userQuestion}', [UserQuestionController::class, 'show']);
        Route::put('/questions/{userQuestion}', [UserQuestionController::class, 'update']);
        Route::get('/candidacies', [CandidacyController::class, 'index']);
        Route::get('/candidacies/{candidacy}', [CandidacyController::class, 'show']);
        Route::put('/candidacies/{candidacy}', [CandidacyController::class, 'update']);
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
        Route::prefix('documents')->middleware('role:Administrateur|Super Admin')->controller(DocumentController::class)->group(function (): void {
            Route::get('/', 'index');
            Route::post('/upload', 'store');
            Route::get('/{document}', 'show');
            Route::put('/{document}', 'update');
            Route::delete('/{document}', 'destroy');
            Route::post('/{document}/sign', 'sign');
            Route::post('/{document}/verify', 'verify');
        });

        Route::apiResource('pharmacists', PharmacistController::class)
            ->middleware(['role:Administrateur|Super Admin'])
            ->except(['index', 'show']);
    });
});
