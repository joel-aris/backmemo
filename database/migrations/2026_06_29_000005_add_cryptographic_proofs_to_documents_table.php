<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->string('current_sha256_hash', 64)->nullable()->after('sha256_hash');
            $table->string('hash_algorithm', 32)->default('SHA-256')->after('current_sha256_hash');
            $table->longText('signature_payload')->nullable()->after('issued_at');
            $table->string('signature', 2048)->change();
            $table->string('signature_algorithm', 80)->nullable()->after('signature');
            $table->longText('public_key')->nullable()->after('signature_algorithm');
            $table->string('public_key_fingerprint', 64)->nullable()->index()->after('public_key');
            $table->timestamp('trusted_timestamp')->nullable()->index()->after('public_key_fingerprint');
            $table->timestamp('integrity_verified_at')->nullable()->after('trusted_timestamp');
            $table->string('integrity_status', 32)->default('pending')->index()->after('integrity_verified_at');
            $table->json('proof_metadata')->nullable()->after('integrity_status');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->dropColumn([
                'current_sha256_hash',
                'hash_algorithm',
                'signature_payload',
                'signature_algorithm',
                'public_key',
                'public_key_fingerprint',
                'trusted_timestamp',
                'integrity_verified_at',
                'integrity_status',
                'proof_metadata',
            ]);
            $table->string('signature', 64)->change();
        });
    }
};
