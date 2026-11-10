<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_assignments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('mission_id');
            $table->uuid('classroom_id');
            $table->uuid('assigned_by')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('status', 16)->default('open');
            $table->timestamps();

            $table->foreign('mission_id')->references('uuid')->on('missions')->cascadeOnDelete();
            $table->foreign('classroom_id')->references('uuid')->on('classrooms')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('uuid')->on('users')->nullOnDelete();
            $table->unique(['mission_id', 'classroom_id']);
            $table->index(['classroom_id', 'status']);
        });

        Schema::table('mission_attempts', function (Blueprint $table) {
            $table->uuid('assignment_id')->nullable()->after('user_id');
            $table->foreign('assignment_id')->references('uuid')->on('mission_assignments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mission_attempts', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropColumn('assignment_id');
        });
        Schema::dropIfExists('mission_assignments');
    }
};
