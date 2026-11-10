<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul Jagat Misi — migrasi dari JagatMISI ke SIMS.
 * Single-tenant: tanpa school_id, user FK ke users.uuid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('classroom_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subject');
            $table->string('grade_level');
            $table->string('mechanic_type');
            $table->text('summary');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->boolean('is_published')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('classroom_id')->references('uuid')->on('classrooms')->nullOnDelete();
            $table->foreign('created_by')->references('uuid')->on('users')->nullOnDelete();
            $table->index(['subject', 'grade_level', 'is_published']);
        });

        Schema::create('mission_steps', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('mission_id');
            $table->string('module_key');
            $table->unsignedSmallInteger('position');
            $table->string('title');
            $table->string('prompt');
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('max_points')->default(0);
            $table->timestamps();

            $table->foreign('mission_id')->references('uuid')->on('missions')->cascadeOnDelete();
            $table->unique(['mission_id', 'module_key']);
            $table->index(['mission_id', 'position']);
        });

        Schema::create('mission_attempts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('mission_id');
            $table->uuid('user_id');
            $table->string('status')->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('result_meta')->nullable();
            $table->timestamps();

            $table->foreign('mission_id')->references('uuid')->on('missions')->cascadeOnDelete();
            $table->foreign('user_id')->references('uuid')->on('users')->cascadeOnDelete();
            $table->index(['mission_id', 'user_id', 'status']);
        });

        Schema::create('mission_attempt_responses', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('mission_attempt_id');
            $table->uuid('mission_step_id');
            $table->string('module_key');
            $table->json('response_payload')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('points_awarded')->default(0);
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->foreign('mission_attempt_id')->references('uuid')->on('mission_attempts')->cascadeOnDelete();
            $table->foreign('mission_step_id')->references('uuid')->on('mission_steps')->cascadeOnDelete();
            $table->index(['mission_attempt_id', 'module_key']);
        });

        Schema::create('mission_badges', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('threshold_xp')->default(0);
            $table->unsignedSmallInteger('threshold_streak')->nullable();
            $table->unsignedSmallInteger('threshold_missions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('mission_student_badges', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_id');
            $table->uuid('badge_id');
            $table->timestamp('earned_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('badge_id')->references('uuid')->on('mission_badges')->cascadeOnDelete();
            $table->unique(['user_id', 'badge_id']);
        });

        Schema::create('mission_collection_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_id');
            $table->uuid('badge_id')->nullable();
            $table->string('code');
            $table->string('name');
            $table->string('kind');
            $table->text('description')->nullable();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('badge_id')->references('uuid')->on('mission_badges')->nullOnDelete();
            $table->unique(['user_id', 'code']);
        });

        Schema::create('mission_activity_logs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('action');
            $table->string('subject_type');
            $table->uuid('subject_id');
            $table->string('causer_type')->nullable();
            $table->uuid('causer_id')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index(['subject_type', 'subject_id']);
        });

        if (! Schema::hasColumn('users', 'leaderboard_visible')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('leaderboard_visible')->default(true)->after('access');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'leaderboard_visible')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('leaderboard_visible');
            });
        }

        Schema::dropIfExists('mission_activity_logs');
        Schema::dropIfExists('mission_collection_items');
        Schema::dropIfExists('mission_student_badges');
        Schema::dropIfExists('mission_badges');
        Schema::dropIfExists('mission_attempt_responses');
        Schema::dropIfExists('mission_attempts');
        Schema::dropIfExists('mission_steps');
        Schema::dropIfExists('missions');
    }
};
