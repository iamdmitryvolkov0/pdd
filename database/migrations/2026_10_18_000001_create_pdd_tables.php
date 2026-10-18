<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->string('source_id');
            $table->unsignedTinyInteger('ticket_number');
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('block_number');
            $table->text('text');
            $table->text('explanation')->nullable();
            $table->string('image_path')->nullable();
            $table->json('topics');
            $table->timestamps();
            $table->unique(['ticket_number', 'position']);
            $table->index(['block_number', 'ticket_number']);
            $table->index('source_id');
        });
        Schema::create('answer_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->text('text');
            $table->boolean('is_correct');
            $table->unique(['question_id', 'position']);
        });
        Schema::create('practice_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('visitor_id')->constrained();
            $table->string('mode', 24);
            $table->string('status', 24)->default('active');
            $table->json('settings')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('elapsed_seconds')->default(0);
            $table->timestamps();
            $table->index(['visitor_id', 'status']);
            $table->index(['mode', 'completed_at']);
        });
        Schema::create('session_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('practice_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained();
            $table->unsignedSmallInteger('position');
            $table->unsignedTinyInteger('stage')->default(0);
            $table->unsignedTinyInteger('block_number');
            $table->unique(['practice_session_id', 'question_id']);
            $table->unique(['practice_session_id', 'position']);
        });
        Schema::create('session_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('practice_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained();
            $table->foreignId('answer_option_id')->constrained();
            $table->boolean('is_correct');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamp('answered_at');
            $table->timestamps();
            $table->unique(['practice_session_id', 'question_id']);
            $table->index(['practice_session_id', 'is_correct']);
            $table->index(['question_id', 'is_correct']);
        });
        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        Schema::create('cache', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });
        Schema::create('cache_locks', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('session_answers');
        Schema::dropIfExists('session_questions');
        Schema::dropIfExists('practice_sessions');
        Schema::dropIfExists('answer_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('visitors');
    }
};
