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
        Schema::create('activity_health_surveillance', function (Blueprint $table) {
            $table->primary(['activity_id', 'health_surveillance_id']); // Chiave primaria composita

            $table->foreignId('activity_id')
                  ->constrained('activities')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreignId('health_surveillance_id')
                  ->constrained('health_surveillances')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // Non sono richiesti timestamps per questa tabella pivot secondo la richiesta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_health_surveillance');
    }
};
 