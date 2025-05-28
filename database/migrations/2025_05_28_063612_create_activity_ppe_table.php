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
        Schema::create('activity_ppe', function (Blueprint $table) {
            $table->primary(['activity_id', 'ppe_id']); // Chiave primaria composita

            $table->foreignId('activity_id')
                  ->constrained('activities') // Assicura che esista un record corrispondente nella tabella 'activities'
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreignId('ppe_id')
                  ->constrained('ppes') // Assicura che esista un record corrispondente nella tabella 'ppes'
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // Non aggiungiamo timestamps qui perché hai detto "non serve lo storico"
            // Se volessi sapere quando è stata fatta l'associazione, potresti aggiungerli:
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_ppe'); 
    }
};
