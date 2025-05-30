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
        Schema::create('profile_section', function (Blueprint $table) {
            $table->id(); // Chiave primaria per la riga della history

            $table->foreignId('profile_id')
                  ->constrained('profiles')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Se l'anagrafica viene eliminata, elimina anche lo storico

            $table->foreignId('section_id')
                  ->constrained('sections')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Se la sezione viene eliminata, elimina anche lo storico relativo

            $table->date('data_inizio_assegnazione');
            $table->date('data_fine_assegnazione')->nullable(); // Null se è l'assegnazione corrente
            $table->text('note')->nullable(); // Note aggiuntive per questa assegnazione

            $table->timestamps(); // created_at e updated_at per la riga della history

            // Assicura che una persona non possa essere in due sezioni nello stesso periodo attivo
            // Questo è un vincolo più complesso, potresti gestirlo a livello applicativo o con trigger DB
            // $table->unique(['anagrafica_id', 'data_fine_assegnazione']); // Esempio base, da raffinare
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_section');
    }
};
