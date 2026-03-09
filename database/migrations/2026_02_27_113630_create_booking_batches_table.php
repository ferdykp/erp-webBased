<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_batches', function (Blueprint $table) {
            $table->id();
            // Hubungkan ke booking_id agar mudah difilter di dashboard
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->integer('batch_number'); // Batch 1, 2, dst
            $table->decimal('quantity', 12, 2); // Jumlah per batch (misal: 20 dari total 100)
            $table->string('unit'); // Mengambil unit dari booking_products (box/drum/sack)
            $table->string('porter_name')->nullable(); // <--- TAMBAHKAN INI
            $table->string('status')->default('waiting'); // waiting, stowed, processing, done
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_batches');
    }
};
