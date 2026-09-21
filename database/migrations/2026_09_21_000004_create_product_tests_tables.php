<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_code')->unique();

            // Product Testing is intentionally independent from the customer/booking tables.
            // A requester may be an individual researcher, an internal OQ activity, or a company.
            $table->string('requester_name')->nullable();
            $table->string('requester_organization')->nullable();
            $table->string('requester_contact')->nullable();

            $table->string('sample_name');
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('dmin', 12, 4)->nullable();
            $table->decimal('dmax', 12, 4)->nullable();
            $table->string('expected_temperature', 100)->nullable();
            $table->decimal('length_cm', 12, 3)->nullable();
            $table->decimal('width_cm', 12, 3)->nullable();
            $table->decimal('height_cm', 12, 3)->nullable();
            $table->decimal('net_weight_kg', 12, 4)->nullable();
            $table->decimal('gross_weight_kg', 12, 4)->nullable();
            $table->text('notes')->nullable();

            // Dedicated test process parameters. There is no warehouse/check-in workflow here.
            $table->foreignId('production_line_id')->nullable()->constrained('production_lines')->nullOnDelete();
            $table->decimal('target_dose', 12, 4)->nullable();
            $table->decimal('beam_speed', 12, 4)->nullable();
            $table->string('loading_mode', 100)->nullable();
            $table->decimal('freq', 12, 4)->nullable();
            $table->decimal('scan_gear', 12, 4)->nullable();
            $table->text('process_notes')->nullable();
            $table->string('status', 30)->default('parameter_pending')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_test_dosimeters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_test_id')->constrained('product_tests')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('dosimeter_number', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->decimal('absorbance', 8, 4)->nullable();
            $table->decimal('dose_kgy', 12, 4)->nullable();
            $table->timestamps();

            $table->unique(['product_test_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_test_dosimeters');
        Schema::dropIfExists('product_tests');
    }
};
