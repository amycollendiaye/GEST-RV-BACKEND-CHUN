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
        Schema::create('service_medicals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nom')->unique(); // index UNIQUE déjà créé

            $table->text('description')->nullable();

            $table->time('heure_ouverture')->index();
            $table->time('heure_fermeture')->index();

            $table->enum('etat', ['DISPONIBLE', 'INDISPONIBLE'])
                ->default('DISPONIBLE')
                ->index();

            // Index composés (optionnel mais puissant)
            $table->index(['etat', 'heure_ouverture'], 'idx_etat_ouverture');
            $table->index(['etat', 'heure_fermeture'], 'idx_etat_fermeture');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_medicals');
    }
};
