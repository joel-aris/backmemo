<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pharmacists', function (Blueprint $table): void {
            $table->string('qr_code_signature', 2048)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pharmacists', function (Blueprint $table): void {
            $table->string('qr_code_signature', 64)->change();
        });
    }
};
