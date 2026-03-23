<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_medecins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('medecin_id');
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();

            $table->foreign('medecin_id')
                ->references('id')
                ->on('personel_hopitals')
                ->cascadeOnDelete();

            $table->index(['medecin_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_medecins');
    }
};
