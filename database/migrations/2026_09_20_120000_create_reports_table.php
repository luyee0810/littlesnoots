<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reports of user-written content — memorial guestbook messages and sitter
     * reviews today, anything else later.
     *
     * Polymorphic because the queue is one list of "things people flagged", and
     * a table per content type would mean a new table and a new screen each
     * time somewhere else accepts writing.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable');

            // Who flagged it. Nullable so a report survives the reporter's account
            // being deleted — the report is still worth acting on.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reason');                       // abusive / spam / ...
            $table->text('notes')->nullable();

            $table->enum('status', ['open', 'actioned', 'dismissed'])
                ->default('open');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            // One report per person per item — flagging twice is a mis-click.
            $table->unique(['reportable_type', 'reportable_id', 'user_id'], 'reports_unique_per_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
