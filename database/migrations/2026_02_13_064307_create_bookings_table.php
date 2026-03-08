<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('qr_token')->unique()->nullable();
            $table->string('booking_code')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->timestamp('arrival_time')->nullable();
            $table->string('pic_warehouse')->nullable();

            // Hapus porter_1 dan porter_2, gunakan satu referensi utama
            // $table->string('porter_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
