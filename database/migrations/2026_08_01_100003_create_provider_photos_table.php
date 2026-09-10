<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors `pet_photos`.
        Schema::create('provider_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            // Which service the photo shows — a sitter's spare room, the taxi's
            // back seat — so a category listing can lead with the right picture.
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_photos');
    }
};
