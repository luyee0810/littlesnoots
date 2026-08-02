<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Service taxonomy — Boarding, Dog Walking, Grooming … (Phase 2).
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 8)->nullable();          // emoji, matching the site's tone

            // Drives the booking form and the price breakdown.
            $table->enum('pricing_unit', ['night', 'day', 'walk', 'session', 'trip', 'hour']);
            $table->boolean('requires_date_range')->default(false);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
