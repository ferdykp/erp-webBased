<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operational_audit_events', function (Blueprint $t) {
            $t->id(); $t->foreignId('admin_id')->nullable()->index();
            $t->string('action', 100); $t->string('subject_type', 100); $t->unsignedBigInteger('subject_id');
            $t->json('details')->nullable(); $t->timestamps();
            $t->index(['subject_type','subject_id']);
        });
        Schema::create('production_parameter_templates', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->foreignId('production_line_id')->constrained('production_lines');
            $t->decimal('target_dose', 10, 4); $t->decimal('beam_speed', 12, 4);
            $t->string('loading_mode'); $t->decimal('freq', 12, 4)->nullable(); $t->decimal('scan_gear', 12, 4)->nullable();
            $t->string('approval_status')->default('draft'); $t->foreignId('created_by')->nullable();
            $t->foreignId('approved_by')->nullable(); $t->timestamp('approved_at')->nullable();
            $t->text('approval_notes')->nullable(); $t->timestamps();
        });
        Schema::create('quality_reviews', function (Blueprint $t) {
            $t->id(); $t->foreignId('booking_id')->constrained('bookings');
            $t->string('decision'); $t->text('notes'); $t->foreignId('reviewed_by'); $t->timestamps();
        });
        Schema::create('order_document_drafts', function (Blueprint $t) {
            $t->id(); $t->foreignId('booking_id')->constrained('bookings'); $t->string('document_type');
            $t->unsignedInteger('version'); $t->json('snapshot'); $t->string('status')->default('draft');
            $t->foreignId('created_by'); $t->timestamps();
            $t->unique(['booking_id','document_type','version'], 'doc_revision_unique');
        });
        Schema::create('pallet_movements', function (Blueprint $t) {
            $t->id(); $t->foreignId('pallet_id')->constrained('pallets'); $t->foreignId('booking_id')->nullable()->constrained('bookings');
            $t->string('from_line')->nullable(); $t->string('from_slot')->nullable();
            $t->string('to_line'); $t->string('to_slot'); $t->text('reason');
            $t->foreignId('moved_by'); $t->timestamps();
        });
    }
    public function down(): void
    {
        foreach (['pallet_movements','order_document_drafts','quality_reviews','production_parameter_templates','operational_audit_events'] as $table) Schema::dropIfExists($table);
    }
};
