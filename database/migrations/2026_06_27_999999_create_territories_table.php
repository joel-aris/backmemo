<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 120)->unique();
            $table->string('code', 10)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('province_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('code', 20);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['province_id', 'name']);
            $table->index(['province_id', 'name']);
        });

        Schema::create('communes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('province_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('city_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['city_id', 'name']);
            $table->index(['province_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
    }
};