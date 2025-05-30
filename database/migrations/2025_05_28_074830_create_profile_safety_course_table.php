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
        Schema::create('profile_safety_course', function (Blueprint $table) {
            $table->id(); // Chiave primaria per ogni record di frequenza

            $table->foreignId('profile_id')
                  ->constrained('profiles')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreignId('safety_course_id') 
                  ->constrained('safety_courses')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->date('attended_date')->comment('Data di frequenza del corso');
            $table->date('expiration_date')->comment('Data di scadenza del corso frequentato');
            $table->string('certificate_number')->nullable()->comment('Numero attestato/certificato');
            $table->text('notes')->nullable();

            $table->timestamps(); // Per tracciare quando è stato creato/aggiornato questo record di frequenza
            $table->softDeletes(); // Per lo storico delle frequenze "eliminate"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_safety_course');
    }
};
