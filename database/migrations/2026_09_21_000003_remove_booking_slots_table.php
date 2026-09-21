<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Booking Slots is no longer part of the application flow.
        Schema::dropIfExists('booking_slots');
    }

    public function down(): void
    {
        // Intentionally left empty. The obsolete module is not restored on rollback.
    }
};
