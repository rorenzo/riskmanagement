<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_ppe', function (Blueprint $table) {
            $table->foreignId('risk_id')->constrained('risks')->onDelete('cascade');
            $table->foreignId('ppe_id')->constrained('ppes')->onDelete('cascade');
            $table->primary(['risk_id', 'ppe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_ppe');
    }
}; 