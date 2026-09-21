<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_type', 20)->default('regular')->after('booking_code')->index();
            $table->string('porter_name')->nullable()->after('pic_warehouse');
            $table->text('test_objective')->nullable()->after('porter_name');
            $table->text('test_notes')->nullable()->after('test_objective');
        });

        Schema::table('booking_batches', function (Blueprint $table) {
            $table->unsignedInteger('total_duration')->nullable()->after('finished_at');
        });
    }

    public function down(): void
    {
        Schema::table('booking_batches', function (Blueprint $table) {
            $table->dropColumn('total_duration');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['booking_type']);
            $table->dropColumn(['booking_type', 'porter_name', 'test_objective', 'test_notes']);
        });
    }
};
