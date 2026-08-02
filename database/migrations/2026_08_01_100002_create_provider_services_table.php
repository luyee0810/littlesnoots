<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // What a provider offers in a given category, and for how much.
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();

            $table->string('title')->nullable();            // overrides the category name
            $table->text('description')->nullable();

            $table->decimal('price', 8, 2);
            $table->enum('price_unit', ['night', 'day', 'walk', 'session', 'trip', 'hour']);
            $table->string('currency', 3)->default('MYR');
            $table->decimal('additional_pet_price', 8, 2)->nullable();

            $table->unsignedSmallInteger('min_units')->default(1);
            $table->unsignedTinyInteger('max_pets')->default(2);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['provider_profile_id', 'service_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_services');
    }
};
