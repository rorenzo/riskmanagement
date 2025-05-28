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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Nome della sezione
            $table->text('descrizione')->nullable(); // Descrizione opzionale

            // Chiave esterna per la relazione con la tabella 'offices'
            $table->foreignId('office_id')
                  ->constrained('offices') // Assicura che la tabella 'offices' esista
                  ->onUpdate('cascade') // Opzionale: cosa fare in caso di update dell'ID dell'ufficio
                  ->onDelete('cascade'); // Opzionale: cosa fare in caso di delete dell'ufficio (es. 'cascade' per cancellare le sezioni, 'restrict' per impedirlo)

            $table->timestamps(); // Campi created_at e updated_at
            $table->softDeletes(); // Campo deleted_at per il soft delete

            // Opzionale: Rendi il nome della sezione univoco all'interno di un ufficio
            // $table->unique(['office_id', 'nome']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
