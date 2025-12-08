<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_jamaah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jamaah_id')
                ->constrained('jamaahs')
                ->onDelete('cascade');

            $table->date('tanggal')->index();
            $table->string('sesi')->nullable();

            // ENUM FINAL
            $table->enum('status', ['BELUM_ABSEN', 'HADIR', 'TIDAK_HADIR']);

            $table->text('catatan')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->comment('Tour Leader yang melakukan absensi')
                ->constrained('tour_leaders')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_jamaah');
    }
};
