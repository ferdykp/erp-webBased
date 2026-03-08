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
        Schema::create('placement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->integer('sequence'); // 1, 2, 3...
            // $table->integer('pallet_use');
            $table->integer('quantity'); // 30, 30, 14...
            $table->string('line')->nullable();
            $table->integer('slot_section')->nullable();
            $table->string('status')->default('planned'); // planned, placed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placement_details');
    }
};
