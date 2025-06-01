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
        Schema::create('profile_ppe', function (Blueprint $table) {
            $table->id();

            $table->foreignId('profile_id')
                  ->constrained('profiles')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreignId('ppe_id')
                  ->constrained('ppes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // 'automatic' (derivato da attività), 'manual' (assegnato direttamente)
            $table->string('assignment_type')->default('automatic');
            // Contiene il nome dell'attività se automatico, o la nota dell'utente se manuale
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->unique(['profile_id', 'ppe_id'], 'profile_ppe_unique_assignment'); // Assicura che un DPI sia assegnato una sola volta a un profilo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_ppe');
    }
};