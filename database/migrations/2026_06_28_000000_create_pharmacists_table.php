<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('public_id', 32)->unique();
            $table->string('photo_path');
            $table->string('first_name', 120);
            $table->string('middle_name', 120)->nullable();
            $table->string('last_name', 120);
            $table->string('ordinal_number', 80)->unique();
            $table->string('sex', 20);
            $table->foreignUuid('province_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('city_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('commune_id')->constrained()->cascadeOnDelete();
            $table->string('professional_address');
            $table->string('professional_phone', 40);
            $table->string('professional_email');
            $table->string('professional_status', 40)->index();
            $table->date('registered_at')->index();
            $table->date('practice_started_at');
            $table->string('license_number', 100)->unique();
            $table->string('license_status', 40)->index();
            $table->date('license_expires_at')->nullable()->index();
            $table->string('pharmacy_establishment', 180);
            $table->string('specialization', 180)->nullable();
            $table->string('verification_hash', 64)->unique();
            $table->string('qr_code_token', 80)->unique();
            $table->string('qr_code_signature', 64);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacists');
    }
};
