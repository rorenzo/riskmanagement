<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_risk', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->foreignId('risk_id')->constrained('risks')->onDelete('cascade');
            $table->primary(['activity_id', 'risk_id']);
            // Non servono timestamps qui generalmente
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_risk');
    }
}; 