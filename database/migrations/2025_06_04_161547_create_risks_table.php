<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esegue le migrazioni.
     */
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nome univoco del rischio');
            $table->text('description')->nullable()->comment('Descrizione dettagliata del rischio');
            $table->string('tipologia')->nullable()->comment('Es: Rischio Generico, Rischio Specifico, Agenti Fisici, Chimico, Biologico, Ergonomico, Trasversale/Organizzativo');
            $table->string('tipo_di_pericolo')->nullable()->comment('Es: Caduta dall\'alto, Rumore, Vibrazioni, Incendio, Elettrico, Movimentazione Manuale Carichi, Stress lavoro-correlato');
            $table->text('misure_protettive')->nullable()->comment('Descrizione delle misure di prevenzione e protezione collettive e individuali');
            $table->timestamps();
            $table->softDeletes(); // Per il soft delete
        });
    }

    /**
     * Annulla le migrazioni.
     */
    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
}; 