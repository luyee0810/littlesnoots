<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One candle per user per memorial — lighting is a toggle, so the unique
        // constraint keeps the count honest.
        Schema::create('memorial_candles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_memorial_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pet_memorial_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorial_candles');
    }
};
