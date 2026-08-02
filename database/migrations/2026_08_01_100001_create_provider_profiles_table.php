<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A service provider is an individual — one profile per user account.
        // Being a provider is *not* a `users.role`: an adopter can also be a sitter.
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('headline');
            $table->text('bio')->nullable();

            // Location
            $table->string('address1')->nullable();
            $table->string('city')->index();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable()->index();
            $table->string('country', 2)->default('MY');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('service_radius_km')->default(10);

            // Home & experience
            $table->unsignedTinyInteger('years_experience')->default(0);
            $table->string('home_type')->nullable();        // apartment / house / farm
            $table->boolean('has_fenced_yard')->default(false);
            $table->boolean('has_own_pets')->default(false);
            $table->boolean('is_smoke_free')->default(true);
            $table->boolean('has_insurance')->default(false);

            // Capacity — species slugs and the `pets.size` vocabulary.
            $table->json('accepts_species')->nullable();
            $table->json('accepts_sizes')->nullable();
            $table->unsignedTinyInteger('max_pets_per_booking')->default(2);

            // Weekly availability mask, e.g. ["mon","tue",…]. Enforced at booking time,
            // not used as a search facet.
            $table->json('available_days')->nullable();

            // Lifecycle
            $table->enum('status', ['draft', 'pending', 'approved', 'suspended'])
                ->default('draft')
                ->index();
            $table->timestamp('published_at')->nullable();

            // Trust — columns land now, populated in Phase 2b.
            $table->timestamp('verified_email_at')->nullable();
            $table->timestamp('verified_phone_at')->nullable();
            $table->timestamp('verified_id_at')->nullable();
            $table->timestamp('background_check_at')->nullable();

            // Denormalised counters, recomputed on review/booking writes.
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('bookings_count')->default(0);
            $table->unsignedTinyInteger('response_rate')->nullable();
            $table->unsignedInteger('response_time_minutes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
