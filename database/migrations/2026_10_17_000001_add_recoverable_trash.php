<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = ['bookings','customers','users','admins','product_tests','pallets','production_lines','porters','warehouse_pics','dosimeter_details','product_test_dosimeters'];
    public function up(): void
    {
        foreach ($this->tables as $table) Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
        Schema::create('trash_entries', function (Blueprint $t) {
            $t->id(); $t->string('model_type'); $t->unsignedBigInteger('record_id'); $t->string('label');
            $t->unsignedBigInteger('deleted_by')->nullable(); $t->json('snapshot');
            $t->unsignedBigInteger('restored_by')->nullable(); $t->timestamp('restored_at')->nullable(); $t->timestamps();
            $t->index(['model_type','record_id']); $t->index(['restored_at','created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('trash_entries');
        foreach ($this->tables as $table) Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
    }
};
