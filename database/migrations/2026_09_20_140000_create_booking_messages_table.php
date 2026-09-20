<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chat between an owner and a sitter, scoped to one booking.
     *
     * Threads hang off the booking rather than off a pair of users: the booking
     * is what they're talking about, it gives the conversation a natural end,
     * and it means access control is exactly BookingPolicy::view — no second
     * set of rules about who may read what.
     */
    public function up(): void
    {
        Schema::create('booking_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'created_at']);
            // "Anything unread for me in this booking?" on every dashboard load.
            $table->index(['booking_id', 'user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_messages');
    }
};
