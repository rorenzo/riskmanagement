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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique(); // Nome del reparto, univoco
            $table->text('descrizione')->nullable(); // Descrizione opzionale
            $table->timestamps(); // Campi created_at e updated_at
            $table->softDeletes(); // Campo deleted_at per il soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices'); 
    }
};
