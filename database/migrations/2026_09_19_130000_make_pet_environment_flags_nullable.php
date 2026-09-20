<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Good with children / dogs / cats" is a three-way answer — yes, no, or
     * nobody knows yet. As NOT NULL DEFAULT false, an untested foster pet was
     * indistinguishable from one known to be bad with children, which is the
     * more damaging of the two to state wrongly. Petfinder models these as
     * nullable for the same reason.
     */
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->boolean('good_with_children')->nullable()->default(null)->change();
            $table->boolean('good_with_dogs')->nullable()->default(null)->change();
            $table->boolean('good_with_cats')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->boolean('good_with_children')->default(false)->change();
            $table->boolean('good_with_dogs')->default(false)->change();
            $table->boolean('good_with_cats')->default(false)->change();
        });
    }
};
