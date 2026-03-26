<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('customer_code')->unique()->nullable();
            $table->string('company_name')->nullable();

            $table->string('industry')->nullable();
            $table->string('npwp')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('website')->nullable();

            $table->text('notes')->nullable();


            $table->boolean('profile_completed')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
