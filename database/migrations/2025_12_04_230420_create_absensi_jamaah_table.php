<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi_jamaah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tourleader_id')
                ->nullable()
                ->constrained('tour_leaders')
                ->nullOnDelete();

            $table->string('judul_absen');

            // Tambahan penting
            $table->string('sesi_absen')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_jamaah');
    }
};
