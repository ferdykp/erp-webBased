<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_dispatches', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->change();
            $table->timestamp('approved_at')->nullable()->change();
            $table->json('quantity_by_unit')->nullable();
        });
        foreach (['pallet_contents', 'placement_details'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('booking_product_id')->nullable()->constrained('booking_products')->restrictOnDelete();
                $table->string('unit', 50)->nullable();
            });
            // Only backfill unambiguous ownership. Never guess a historic product.
            DB::table($name)->orderBy('id')->chunkById(200, function ($rows) use ($name) {
                foreach ($rows as $row) {
                    $products = DB::table('booking_products')->where('booking_id', $row->booking_id)->get();
                    if ($products->count() !== 1 && $name === 'pallet_contents') {
                        $products = $products->where('product_name', $row->product_name);
                    }
                    if ($products->count() === 1) {
                        DB::table($name)->where('id', $row->id)->update([
                            'booking_product_id' => $products->first()->id, 'unit' => $products->first()->unit,
                        ]);
                    }
                }
            });
        }
        Schema::table('irradiation_sessions', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->text('void_reason')->nullable();
        });
    }

    public function down(): void
    {
        // Approval nullability is intentionally retained: direct dispatch history has no approver.
        Schema::table('order_dispatches', fn (Blueprint $table) => $table->dropColumn('quantity_by_unit'));
        foreach (['pallet_contents', 'placement_details'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropForeign(['booking_product_id']);
                $table->dropColumn(['booking_product_id', 'unit']);
            });
        }
        Schema::table('irradiation_sessions', fn (Blueprint $table) => $table->dropColumn(['voided_at', 'voided_by', 'void_reason']));
    }
};
