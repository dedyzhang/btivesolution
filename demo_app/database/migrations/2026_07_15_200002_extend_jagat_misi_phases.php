<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->string('status', 16)->default('draft')->after('is_published');
            $table->json('objectives')->nullable()->after('summary');
            $table->boolean('requires_reflection')->default(true)->after('is_published');
            $table->boolean('visible_to_teachers')->default(false)->after('requires_reflection');
        });

        Schema::create('mission_reflection_prompts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('mission_id');
            $table->unsignedSmallInteger('position')->default(1);
            $table->text('prompt_text');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->foreign('mission_id')->references('uuid')->on('missions')->cascadeOnDelete();
            $table->index(['mission_id', 'position']);
        });

        Schema::create('mission_reflections', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('mission_attempt_id');
            $table->uuid('user_id');
            $table->text('understand')->nullable();
            $table->text('barrier')->nullable();
            $table->text('next_step')->nullable();
            $table->string('mood', 32)->nullable();
            $table->json('prompts_meta')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('mission_attempt_id')->references('uuid')->on('mission_attempts')->cascadeOnDelete();
            $table->foreign('user_id')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('uuid')->on('users')->nullOnDelete();
            $table->unique('mission_attempt_id');
        });

        Schema::create('mission_concept_mastery', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('user_id');
            $table->string('concept_key');
            $table->string('concept_label');
            $table->string('subject');
            $table->unsignedSmallInteger('score')->default(0);
            $table->string('level', 16)->default('support');
            $table->unsignedSmallInteger('missions_count')->default(0);
            $table->unsignedSmallInteger('reflections_count')->default(0);
            $table->text('recommendation')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('uuid')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'concept_key']);
            $table->index(['subject', 'level']);
        });

        Schema::create('mission_item_bank', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('created_by')->nullable();
            $table->string('type', 32);
            $table->string('title');
            $table->text('content')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();

            $table->foreign('created_by')->references('uuid')->on('users')->nullOnDelete();
            $table->index(['created_by', 'type']);
        });

        if (! Schema::hasColumn('users', 'mission_avatar_config')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('mission_avatar_config')->nullable()->after('leaderboard_visible');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'mission_avatar_config')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('mission_avatar_config');
            });
        }

        Schema::dropIfExists('mission_item_bank');
        Schema::dropIfExists('mission_concept_mastery');
        Schema::dropIfExists('mission_reflections');
        Schema::dropIfExists('mission_reflection_prompts');

        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['status', 'objectives', 'requires_reflection', 'visible_to_teachers']);
        });
    }
};
