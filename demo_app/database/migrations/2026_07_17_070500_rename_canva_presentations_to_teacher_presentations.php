<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('canva_presentations') && ! Schema::hasTable('teacher_presentations')) {
            Schema::rename('canva_presentations', 'teacher_presentations');
        }

        if (Schema::hasTable('teacher_presentations')) {
            Schema::table('teacher_presentations', function (Blueprint $table) {
                if (Schema::hasColumn('teacher_presentations', 'canva_url')) {
                    $table->dropColumn('canva_url');
                }
                if (! Schema::hasColumn('teacher_presentations', 'slides')) {
                    $table->json('slides')->nullable()->after('outline');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_presentations')) {
            Schema::table('teacher_presentations', function (Blueprint $table) {
                if (Schema::hasColumn('teacher_presentations', 'slides')) {
                    $table->dropColumn('slides');
                }
                if (! Schema::hasColumn('teacher_presentations', 'canva_url')) {
                    $table->string('canva_url', 500)->nullable()->after('notes');
                }
            });
        }

        if (Schema::hasTable('teacher_presentations') && ! Schema::hasTable('canva_presentations')) {
            Schema::rename('teacher_presentations', 'canva_presentations');
        }
    }
};
