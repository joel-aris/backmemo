<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pharmacists', function (Blueprint $table): void {
            $table->longText('public_key')->nullable()->after('qr_code_signature');
            $table->string('public_key_fingerprint', 64)->nullable()->index()->after('public_key');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacists', function (Blueprint $table): void {
            $table->dropColumn(['public_key', 'public_key_fingerprint']);
        });
    }
};
