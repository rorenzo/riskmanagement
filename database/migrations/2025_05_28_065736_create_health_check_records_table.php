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
        Schema::create('health_check_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('profile_id')
                  ->constrained('profiles') // Tabella 'profiles' (ex anagrafiche)
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Se il profilo viene eliminato, elimina anche i suoi record di controllo

            $table->foreignId('health_surveillance_id')
                  ->constrained('health_surveillances') // Tabella 'health_surveillances'
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Se il tipo di sorveglianza viene eliminato, elimina i record associati

            // Opzionale: per tracciare quale attività ha reso necessaria questa sorveglianza specifica
            // Potrebbe essere null se una sorveglianza è assegnata direttamente al profilo per altri motivi
            $table->foreignId('activity_id')->nullable()
                  ->constrained('activities')
                  ->onUpdate('cascade')
                  ->onDelete('set null'); // Se l'attività viene eliminata, non invalida il record di sorveglianza

            $table->date('check_up_date')->comment('Data della visita/controllo');
            $table->date('expiration_date')->comment('Data di scadenza calcolata');
            $table->string('outcome')->nullable()->comment('Esito della visita, es: Idoneo, Idoneo con prescrizioni, Non idoneo');
            $table->text('notes')->nullable()->comment('Eventuali note sulla visita o prescrizioni');
            // $table->string('status')->default('Valida')->comment('Valida, Scaduta, Superata, Annullata'); // Potrebbe essere derivato

            $table->timestamps();
            $table->softDeletes(); // Per mantenere lo storico anche delle registrazioni "eliminate"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_check_records');
    }
};
 