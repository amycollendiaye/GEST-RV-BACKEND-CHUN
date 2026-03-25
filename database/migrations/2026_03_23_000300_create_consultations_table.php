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
        Schema::create('consultations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('rendez_vous_id');
            $table->uuid('patient_id');
            $table->uuid('medecin_id');

            $table->string('tension_artielle', 20);
            $table->decimal('poids', 6, 2);
            $table->decimal('temperature', 4, 1);
            $table->text('sumptomes');
            $table->text('diagnostic');
            $table->text('traitement');
            $table->text('observations')->nullable();
            $table->timestamp('date_heure');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');

            $table->foreign('medecin_id')
                ->references('id')
                ->on('personel_hopitals')
                ->onDelete('cascade');

            $table->index(['medecin_id', 'date_heure']);
            $table->index(['patient_id', 'date_heure']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
