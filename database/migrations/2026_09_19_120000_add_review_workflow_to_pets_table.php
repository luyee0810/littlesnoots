<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Listings are now created by rescuers and fosterers, not only staff, so
     * they need moderating before they go public.
     *
     * `status` describes the animal (available / adopted); `review_status`
     * describes the listing. Approval is what sets `published_at`, which the
     * published() scope already gates the public site on.
     */
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->enum('review_status', ['draft', 'submitted', 'approved', 'rejected'])
                ->default('draft')
                ->after('status');
            $table->text('review_notes')->nullable()->after('review_status');
            $table->foreignId('reviewed_by')->nullable()->after('review_notes')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->index(['review_status', 'created_at']);
        });

        // Anything already published predates moderation — treat it as approved.
        DB::table('pets')->whereNotNull('published_at')->update(['review_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropIndex(['review_status', 'created_at']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_status', 'review_notes', 'reviewed_at']);
        });
    }
};
