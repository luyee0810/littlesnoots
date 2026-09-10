<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A tribute page for a pet that has passed away. Created by a signed-in
        // user; the memorial is public so friends and family can visit, light a
        // candle and leave a message.
        Schema::create('pet_memorials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('pet_name');
            $table->string('species')->nullable();     // free text — "Cat", "Golden Retriever", …
            $table->string('photo_path')->nullable();   // public-disk path or bundled /images path
            $table->date('born_on')->nullable();
            $table->date('passed_on')->nullable();
            $table->text('tribute');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_memorials');
    }
};
