<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master jenis dokumen perangkat ajar (RPP, Modul Ajar, Prota, dst) — bebas ditambah admin.
        Schema::create('perangkat_list', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('perangkat');
            $table->timestamps();
        });

        // File yang diupload guru per jenis perangkat. Satu guru boleh punya banyak
        // file utk satu jenis dokumen (tidak dibatasi 1:1).
        Schema::create('perangkat_guru', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('id_guru', 36);   // gurus.uuid
            $table->string('id_list', 36);   // perangkat_list.uuid
            $table->string('nama_asli');     // nama file asli (tampilan)
            $table->text('file');            // path relatif storage disk 'public'
            $table->timestamps();

            $table->index(['id_guru', 'id_list']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat_guru');
        Schema::dropIfExists('perangkat_list');
    }
};
