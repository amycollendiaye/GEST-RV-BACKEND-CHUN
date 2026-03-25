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
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('patient_id');
            $table->uuid('service_medical_id');
            $table->uuid('medecin_id')->nullable();

            $table->timestamp('date_rendez_vous')->nullable()->index();
            $table->string('motif', 500);
            $table->enum('statut', ['PLANIFIER', 'FAIT', 'ANNULER'])->default('PLANIFIER')->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');

            $table->foreign('service_medical_id')
                ->references('id')
                ->on('service_medicals')
                ->onDelete('cascade');

            $table->foreign('medecin_id')
                ->references('id')
                ->on('personel_hopitals')
                ->nullOnDelete();

            $table->index(['service_medical_id', 'statut']);
            $table->index(['patient_id', 'statut']);
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->foreign('rendez_vous_id')
                ->references('id')
                ->on('rendez_vous')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['rendez_vous_id']);
        });

        Schema::dropIfExists('rendez_vous');
    }
};
