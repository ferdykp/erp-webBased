<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Utama Record Dosimeter
        Schema::create('dosimeter_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->integer('tablet_quantity')->default(0); // Menyimpan input misal: 9
            $table->timestamps();
        });

        // Tabel Detail Nilai Absorbance & Hasil Hitung Dose
        Schema::create('dosimeter_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosimeter_record_id')->constrained('dosimeter_records')->onDelete('cascade');
            $table->integer('tablet_number'); // Tablet ke-1, ke-2, dst.
            $table->decimal('absorbance', 8, 4)->nullable(); // Menyimpan input nilai absorbance
            $table->decimal('dose_kgy', 8, 2)->nullable(); // Menyimpan hasil kalkulasi Dose (kGy)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosimeter_details');
        Schema::dropIfExists('dosimeter_records');
    }
};
