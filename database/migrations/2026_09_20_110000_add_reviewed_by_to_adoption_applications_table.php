<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Applications are handled by whoever listed the pet, so `reviewed_at`
     * alone doesn't say who decided — needed once staff can step in on a
     * rescuer's behalf. Mirrors pets and provider_profiles.
     */
    public function up(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->after('staff_notes')
                ->constrained('users')->nullOnDelete();

            $table->index(['pet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropIndex(['pet_id', 'status']);
            $table->dropConstrainedForeignId('reviewed_by');
        });
    }
};
