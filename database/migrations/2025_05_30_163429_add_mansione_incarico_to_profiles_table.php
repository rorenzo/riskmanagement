<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('incarico')->nullable()->after('cf')->comment('Es: direttore, capo ufficio, capo sezione, addetto');
            $table->text('mansione')->nullable()->after('incarico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('incarico');
            $table->dropColumn('mansione');
        });
    }
};