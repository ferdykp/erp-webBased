<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dosimeter_details', function (Blueprint $table) {
            // Historic measurements remain unassigned; do not infer a batch from row order.
            $table->foreignId('booking_batch_id')->nullable()->constrained('booking_batches')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dosimeter_details', function (Blueprint $table) {
            $table->dropForeign(['booking_batch_id']);
            $table->dropColumn('booking_batch_id');
        });
    }
};
