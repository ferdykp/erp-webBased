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
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade');

            $table->string('product_name');
            $table->string('product_type');
            $table->bigInteger('quantity');
            $table->string('unit');
            // $table->string('target_dose');
            $table->string('dmin');
            $table->string('dmax');
            $table->string('dimension_pack');
            $table->string('gross_weight_per_pcs');
            $table->string('expect_temp')->nullable();
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
