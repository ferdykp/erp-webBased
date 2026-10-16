<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('irradiation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_batch_id')->constrained('booking_batches')->cascadeOnDelete();
            $table->foreignId('production_line_id')->constrained('production_lines');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->unsignedInteger('duration_minutes');
            $table->decimal('target_dose', 12, 4)->nullable();
            $table->decimal('beam_speed', 12, 4)->nullable();
            $table->decimal('freq', 12, 4)->nullable();
            $table->decimal('scan_gear', 12, 4)->nullable();
            $table->string('loading_mode')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamps();
            $table->index(['production_line_id', 'started_at', 'ended_at'], 'session_machine_time_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('irradiation_sessions'); }
};
