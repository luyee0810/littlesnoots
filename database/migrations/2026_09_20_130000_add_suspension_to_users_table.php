<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Suspension — the missing half of moderation.
     *
     * Reports let staff remove *content*; nothing could stop the *person* who
     * wrote it from posting again. A suspended account stays intact (their
     * listings, bookings and history are still needed) but can't sign in.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('role');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
            $table->foreignId('suspended_by')->nullable()->after('suspension_reason')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('suspended_by');
            $table->dropColumn(['suspended_at', 'suspension_reason']);
        });
    }
};
