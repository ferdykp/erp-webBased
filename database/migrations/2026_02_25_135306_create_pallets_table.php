<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pallets', function (Blueprint $table) {
            $table->id();
            // $table->string('pallet_number')->unique(); // Palet 01 - Palet 10
            $table->string('status')->default('empty'); // empty, filled
            $table->foreignId('current_booking_id')->nullable()->constrained('bookings')->nullOnDelete();

            $table->string('line')->nullable(); // Contoh: Line A, Line B
            $table->integer('slot_section')->nullable(); // Petak 1 sampai 5
            // $table->integer('box_capacity')->default(10); // Kapasitas 10 box per palet
            // $table->integer('box_capacity')->nullable();
            $table->integer('filled_boxes')->default(0); // Jumlah box yang terisi saat ini
            $table->unique(['line', 'slot_section']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallets');
    }
};
