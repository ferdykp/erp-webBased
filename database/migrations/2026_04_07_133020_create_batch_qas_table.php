<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_qas', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel batches (Asumsi nama tabel: batches)
            $table->foreignId('batch_id')
                ->constrained('booking_batches')
                ->onDelete('cascade');
            // Step 2 Form Data
            $table->decimal('actual_dose', 8, 2);
            $table->enum('visual_check', ['pass', 'fail'])->default('pass');
            $table->enum('indicator_check', ['changed', 'no_change'])->default('changed');

            // Damage Report
            $table->boolean('is_damaged')->default(false);
            $table->integer('damaged_qty')->nullable();
            $table->string('damage_description')->nullable();

            $table->text('qa_notes')->nullable();
            $table->timestamp('inspected_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_qas');
    }
};
