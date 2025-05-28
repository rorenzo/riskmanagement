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
        Schema::create('ppes', function (Blueprint $table) { // Nome tabella al plurale: ppes
            $table->id();
            $table->string('name')->unique(); // Nome del DPI, univoco
            $table->text('description')->nullable(); // Descrizione opzionale
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppes');
    }
};
 