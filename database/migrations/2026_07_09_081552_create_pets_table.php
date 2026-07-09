<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            $table->foreignId('species_id')->constrained()->restrictOnDelete();
            $table->foreignId('breed_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('sex', ['unknown', 'male', 'female'])->default('unknown');
            $table->enum('size', ['small', 'medium', 'large', 'extra_large'])->nullable();
            $table->unsignedSmallInteger('age_months')->nullable();   // approx age
            $table->string('color')->nullable();

            // Adoption lifecycle
            $table->enum('status', ['available', 'pending', 'adopted', 'unavailable'])
                ->default('available')
                ->index();
            $table->decimal('adoption_fee', 8, 2)->default(0);

            // Health / behaviour flags used for filtering
            $table->boolean('vaccinated')->default(false);
            $table->boolean('sterilized')->default(false);
            $table->boolean('good_with_kids')->default(false);
            $table->boolean('good_with_pets')->default(false);

            $table->string('location')->nullable();          // shelter / city
            $table->text('description')->nullable();

            // Who manages this listing (shelter staff / owner). Nullable for seeded demo data.
            $table->foreignId('listed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
