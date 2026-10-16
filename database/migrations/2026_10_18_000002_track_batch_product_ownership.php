<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
return new class extends Migration {
    public function up(): void {
        Schema::table('booking_batches', fn(Blueprint $table) => $table->foreignId('booking_product_id')->nullable()->constrained('booking_products')->restrictOnDelete());
        DB::table('booking_batches')->orderBy('id')->chunkById(200, function($rows) {
            foreach($rows as $row) {
                $products=DB::table('booking_products')->where('booking_id',$row->booking_id)->get();
                if($products->count()===1) DB::table('booking_batches')->where('id',$row->id)->update(['booking_product_id'=>$products->first()->id]);
            }
        });
    }
    public function down(): void { Schema::table('booking_batches', function(Blueprint $table) { $table->dropForeign(['booking_product_id']); $table->dropColumn('booking_product_id'); }); }
};
