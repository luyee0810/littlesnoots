<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The `US` default is a leftover from the Petfinder model this table was
     * shaped on. Little Snoots is Malaysian, and nobody filling in a shelter
     * should have to correct the country.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('country', 2)->default('MY')->change();
        });

        DB::table('organizations')->where('country', 'US')->update(['country' => 'MY']);
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('country', 2)->default('US')->change();
        });
    }
};
