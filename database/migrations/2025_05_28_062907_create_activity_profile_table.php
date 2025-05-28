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
        Schema::create('activity_profile', function (Blueprint $table) {
            $table->primary(['activity_id', 'profile_id']); // Chiave primaria composita

            $table->foreignId('activity_id')
                  ->constrained('activities')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreignId('profile_id')
                  ->constrained('profiles')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); 

            $table->timestamps(); // Opzionale: se vuoi tracciare quando è stata fatta l'associazione
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_profile');
    }
};
