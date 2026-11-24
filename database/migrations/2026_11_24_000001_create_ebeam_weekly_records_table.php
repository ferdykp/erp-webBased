<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ebeam_weekly_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_line_id')->constrained('production_lines')->restrictOnDelete();
            $table->date('record_date');
            $table->date('week_start');
            $table->json('readings')->nullable();
            $table->timestamps();

            $table->unique(['production_line_id', 'week_start'], 'ebeam_weekly_machine_week_unique');
            $table->index('record_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebeam_weekly_records');
    }
};
