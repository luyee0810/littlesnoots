<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A sitter's right of reply. One reply per review, on the record — the
     * fair answer to public feedback is a public response, not deletion.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('provider_reply')->nullable()->after('body');
            $table->timestamp('replied_at')->nullable()->after('provider_reply');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['provider_reply', 'replied_at']);
        });
    }
};
