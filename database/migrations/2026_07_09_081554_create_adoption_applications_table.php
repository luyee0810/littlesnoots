<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adoption_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();

            // Logged-in adopter (optional — guests can apply with contact details)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->text('message')->nullable();
            $table->string('home_type')->nullable();          // apartment / house
            $table->boolean('has_other_pets')->default(false);

            $table->enum('status', ['pending', 'reviewing', 'approved', 'rejected', 'withdrawn'])
                ->default('pending')
                ->index();
            $table->text('staff_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adoption_applications');
    }
};
