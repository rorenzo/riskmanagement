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
        Schema::create('health_surveillances', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nome della sorveglianza, univoco
            $table->text('description')->nullable(); // Descrizione opzionale
            $table->integer('duration_years')->nullable()->comment('Ogni quanti anni deve essere ricontrollata'); // Durata in anni
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_surveillances');
    }
};
 