<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pharmacist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 180);
            $table->string('type', 60)->index();
            $table->string('path');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size');
            $table->string('sha256_hash', 64)->unique();
            $table->date('issued_at')->index();
            $table->string('signature', 64);
            $table->string('qr_code_token', 80)->unique();
            $table->string('status', 40)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
