<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('cv_mime_type')->nullable();
            $table->integer('cv_size')->nullable();
            $table->string('motivation_letter_path')->nullable();
            $table->string('motivation_letter_mime_type')->nullable();
            $table->integer('motivation_letter_size')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidacies');
    }
};
