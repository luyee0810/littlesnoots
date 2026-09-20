<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sitter profiles already had a `status` (draft/pending/approved/suspended)
     * but nowhere to record *why* a moderator held one back, so a rejected
     * sitter had no idea what to fix. Mirrors the columns pets use.
     */
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->text('review_notes')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('review_notes')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_notes', 'reviewed_at']);
        });
    }
};
