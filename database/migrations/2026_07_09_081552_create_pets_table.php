<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors the Petfinder "animal" object.
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('species_id')->constrained()->restrictOnDelete();

            // Breeds: primary + optional secondary, plus mixed / unknown flags
            $table->foreignId('breed_id')->nullable()->constrained('breeds')->nullOnDelete();
            $table->foreignId('secondary_breed_id')->nullable()->constrained('breeds')->nullOnDelete();
            $table->boolean('breed_mixed')->default(false);
            $table->boolean('breed_unknown')->default(false);

            // Core characteristics
            $table->enum('age_group', ['baby', 'young', 'adult', 'senior'])->nullable()->index();
            $table->unsignedSmallInteger('age_months')->nullable();   // optional precise age
            $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown');
            $table->enum('size', ['small', 'medium', 'large', 'extra_large'])->nullable();
            $table->enum('coat', ['hairless', 'short', 'medium', 'long', 'wire', 'curly'])->nullable();
            $table->string('color')->nullable();              // primary colour
            $table->string('secondary_color')->nullable();

            // Adoption lifecycle
            $table->enum('status', ['available', 'pending', 'adopted', 'found', 'unavailable'])
                ->default('available')
                ->index();
            $table->decimal('adoption_fee', 8, 2)->default(0);

            // Attributes (Petfinder "attributes")
            $table->boolean('spayed_neutered')->default(false);
            $table->boolean('shots_current')->default(false);
            $table->boolean('house_trained')->default(false);
            $table->boolean('declawed')->default(false);
            $table->boolean('special_needs')->default(false);

            // Good in a home with (Petfinder "environment")
            $table->boolean('good_with_children')->default(false);
            $table->boolean('good_with_dogs')->default(false);
            $table->boolean('good_with_cats')->default(false);

            // Personality tags (Petfinder "tags") e.g. ["Friendly", "Playful"]
            $table->json('tags')->nullable();

            $table->string('location')->nullable();          // city / area shown on card
            $table->text('description')->nullable();

            // Who manages this listing (shelter staff / owner). Nullable for seeded demo data.
            $table->foreignId('listed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
