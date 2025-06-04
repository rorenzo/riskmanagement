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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('grado')->nullable();
            $table->string('nome');
            $table->string('cognome');
            $table->enum('sesso', ['M', 'F', 'Altro'])->nullable();

            $table->string('luogo_nascita_citta')->nullable();
            $table->string('luogo_nascita_provincia', 2)->nullable();
            $table->string('luogo_nascita_nazione')->nullable()->default('Italia');
            $table->date('data_nascita')->nullable();

            $table->string('email')->unique()->nullable();
            $table->string('cellulare')->unique()->nullable();
            $table->string('cf', 16)->unique()->nullable();

            $table->string('residenza_via')->nullable();
            $table->string('residenza_citta')->nullable();
            $table->string('residenza_provincia', 2)->nullable();
            $table->string('residenza_cap', 5)->nullable();
            $table->string('residenza_nazione')->nullable()->default('Italia');

            // Non c'è più 'current_section_id' qui.
            // La sezione corrente si deduce dallo storico in 'profile_section'
            // e dallo stato in 'periodi_impiego'.

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
