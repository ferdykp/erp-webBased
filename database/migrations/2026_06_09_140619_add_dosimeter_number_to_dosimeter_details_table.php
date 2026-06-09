<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosimeter_details', function (Blueprint $table) {
            // Menambahkan kolom dosimeter_number setelah tablet_number
            $table->string('dosimeter_number')->nullable()->after('tablet_number');
        });
    }

    public function down(): void
    {
        Schema::table('dosimeter_details', function (Blueprint $table) {
            $table->dropColumn('dosimeter_number');
        });
    }
};
