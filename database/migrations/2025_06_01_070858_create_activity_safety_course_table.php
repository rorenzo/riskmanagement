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
        Schema::create('activity_safety_course', function (Blueprint $table) {
            $table->primary(['activity_id', 'safety_course_id']); // Chiave primaria composita

            $table->foreignId('activity_id')
                  ->constrained('activities')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreignId('safety_course_id')
                  ->constrained('safety_courses')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // Non sono richiesti timestamps per questa tabella pivot secondo le richieste precedenti
            // Se vuoi tracciare quando è stata fatta l'associazione, aggiungi:
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_safety_course');
    }
};