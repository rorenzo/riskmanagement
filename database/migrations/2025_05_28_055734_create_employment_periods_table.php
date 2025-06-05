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
        Schema::create('employment_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')
                  ->constrained('profiles')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Se l'anagrafica viene eliminata, elimina anche i suoi periodi di impiego

            $table->date('data_inizio_periodo');
            $table->date('data_fine_periodo')->nullable();

            $table->string('tipo_ingresso')->comment('Es: Assunzione, Rientro da trasferimento, Reintegro');
            $table->string('tipo_uscita')->nullable()->comment('Es: Dimissioni, Licenziamento, Pensionamento, Trasferimento in uscita');
            $table->string('incarico')->nullable()->comment('Es: direttore, capo ufficio, capo sezione, addetto');
            $table->text('mansione')->nullable();
            $table->string('ente_provenienza_trasferimento')->nullable()->comment('Nome ente da cui proviene per trasferimento');
            $table->string('ente_destinazione_trasferimento')->nullable()->comment('Nome ente verso cui va per trasferimento');

            $table->text('note_periodo')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_periods');
    }
};
