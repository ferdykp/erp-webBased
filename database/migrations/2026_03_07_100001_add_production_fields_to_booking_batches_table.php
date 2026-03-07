<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Menambahkan kolom parameter produksi (Layer 3) ke tabel booking_batches.
     */
    public function up(): void
    {
        Schema::table('booking_batches', function (Blueprint $table) {
            $table->foreignId('production_line_id')
                ->nullable()
                ->after('status')
                ->constrained('production_lines')
                ->nullOnDelete();

            $table->decimal('target_dose', 10, 4)->nullable()->after('production_line_id');
            $table->decimal('beam_speed', 10, 4)->nullable()->after('target_dose');
            $table->string('loading_mode')->nullable()->after('beam_speed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_batches', function (Blueprint $table) {
            $table->dropForeign(['production_line_id']);
            $table->dropColumn([
                'production_line_id',
                'target_dose',
                'beam_speed',
                'loading_mode',
            ]);
        });
    }
};
