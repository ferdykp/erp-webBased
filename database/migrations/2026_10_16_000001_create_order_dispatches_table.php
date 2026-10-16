<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->unique();
            $table->string('status', 30)->default('approved');
            $table->foreignId('approved_by');
            $table->timestamp('approved_at');
            $table->text('approval_notes')->nullable();
            $table->foreignId('dispatched_by')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->string('reference', 150)->nullable();
            $table->text('dispatch_notes')->nullable();
            $table->unsignedInteger('dispatched_quantity')->nullable();
            $table->json('released_locations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_dispatches');
    }
};
