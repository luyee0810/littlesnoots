<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Direct booking: the owner picks a provider, the provider accepts or declines.
        // No money changes hands on the platform in Phase 2 — the price columns record
        // what the two parties agreed, for reference only.
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();      // public code, e.g. "TFC-8KQ2ZP"

            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_service_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // When
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();       // null for single-shot services
            $table->unsignedSmallInteger('unit_quantity')->default(1);
            $table->string('unit_label');                   // night / walk / session …

            // Pet snapshot — survives edits to the owner's pet profiles.
            $table->string('pet_name');
            $table->foreignId('pet_species_id')->nullable()->constrained('species')->nullOnDelete();
            $table->string('pet_breed')->nullable();
            $table->string('pet_size')->nullable();
            $table->unsignedTinyInteger('pet_count')->default(1);
            $table->text('pet_notes')->nullable();

            // Owner snapshot + where the service happens (owner's home for sitting/walking).
            $table->string('owner_name');
            $table->string('owner_email');
            $table->string('owner_phone')->nullable();
            $table->string('service_address')->nullable();
            $table->string('service_city')->nullable();

            // Price snapshot — calculated server-side at booking time, never recomputed.
            $table->decimal('unit_price', 8, 2);
            $table->decimal('additional_pet_price', 8, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('MYR');

            // Flow
            $table->enum('status', [
                'pending', 'accepted', 'declined', 'in_progress', 'completed',
                'cancelled_by_owner', 'cancelled_by_provider', 'expired',
            ])->default('pending');

            $table->text('message')->nullable();            // from the owner
            $table->text('provider_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['provider_profile_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
