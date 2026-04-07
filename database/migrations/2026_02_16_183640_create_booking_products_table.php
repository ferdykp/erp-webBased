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
        Schema::create('booking_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');

            $table->string('product_name');
            $table->string('product_type');
            $table->integer('quantity'); // Gunakan integer jika tidak ada qty setengah (0.5)
            $table->string('unit');

            $table->decimal('dmin', 8, 2); // kGy biasanya cukup 8 digit total
            $table->decimal('dmax', 8, 2);
            $table->string('dimension_pack');
            $table->string('expect_temp')->nullable();

            // Volume cukup 2 atau 3 desimal saja agar tidak penuh nol
            $table->decimal('vol_per_pcs', 15, 2)->nullable();
            $table->decimal('vol_total', 15, 2)->nullable();

            $table->decimal('net_weight_pcs', 15, 2)->nullable();
            $table->decimal('total_net_weight', 15, 2)->nullable();
            $table->decimal('gross_weight_per_pcs', 15, 2)->nullable();
            $table->decimal('total_gross_weight', 15, 2)->nullable();

            // Density HARUS punya presisi tinggi (misal 6 atau 8) karena angkanya sering sangat kecil
            $table->decimal('density_gross', 15, 6);
            $table->decimal('density_nett', 15, 6);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_products');
    }
};
