<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('category')->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_answered')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['is_answered', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_questions');
    }
};
