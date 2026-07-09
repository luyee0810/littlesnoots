<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shelters / rescue groups — mirrors the Petfinder "organization" object.
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['shelter', 'rescue', 'foster', 'individual'])->default('shelter');

            // Contact
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Address
            $table->string('address1')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country', 2)->default('US');

            // About
            $table->text('mission_statement')->nullable();
            $table->text('adoption_policy')->nullable();
            $table->json('hours')->nullable();          // { mon: "9-5", ... }

            // Social
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
