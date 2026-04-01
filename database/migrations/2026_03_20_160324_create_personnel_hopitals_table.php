<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personel_hopitals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nom');
            $table->string('prenom');

            $table->string('email')->unique();
            $table->string('telephone')->unique();

            $table->string('specialite')->nullable()->index();
            $table->string('matricule')->unique();

            $table->string('activation_token')->nullable()->unique();
            $table->timestamp('activation_token_expires_at')->nullable();

            $table->enum('role', ['MEDECIN', 'SECRETAIRE', 'ADMIN'])->index();
            $table->enum('statut', ['ACTIF', 'INACTIF', 'ENCONGE'])
                ->default('ACTIF')
                ->index();

            $table->uuid('service_medical_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_medical_id')
                ->references('id')
                ->on('service_medicals')
                ->nullOnDelete();

            $table->index(['role', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personel_hopitals');
    }
};
