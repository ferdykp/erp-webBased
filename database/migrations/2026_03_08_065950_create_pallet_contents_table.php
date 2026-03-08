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
        Schema::create('pallet_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pallet_id')->constrained('pallets'); // Ke ID lokasi
            $table->foreignId('booking_id')->constrained();      // Ke ID Order
            $table->string('product_name');                     // Nama produk
            $table->integer('quantity');                        // Jumlah box
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallet_contents');
    }
};
