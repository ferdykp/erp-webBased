<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_tests') && Schema::hasColumn('product_tests', 'quantity')) {
            // Quantity is a count, so old decimal-looking values are normalized first.
            DB::statement('UPDATE product_tests SET quantity = GREATEST(1, ROUND(quantity)) WHERE quantity IS NOT NULL');
            Schema::table('product_tests', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->nullable()->change();
            });
        }

        if (Schema::hasTable('booking_batches') && Schema::hasColumn('booking_batches', 'quantity')) {
            DB::statement('UPDATE booking_batches SET quantity = GREATEST(1, ROUND(quantity)) WHERE quantity IS NOT NULL');
            Schema::table('booking_batches', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_batches') && Schema::hasColumn('booking_batches', 'quantity')) {
            Schema::table('booking_batches', function (Blueprint $table) {
                $table->decimal('quantity', 12, 2)->change();
            });
        }

        if (Schema::hasTable('product_tests') && Schema::hasColumn('product_tests', 'quantity')) {
            Schema::table('product_tests', function (Blueprint $table) {
                $table->decimal('quantity', 12, 3)->nullable()->change();
            });
        }
    }
};
