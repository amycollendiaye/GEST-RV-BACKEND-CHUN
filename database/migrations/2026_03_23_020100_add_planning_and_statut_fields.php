<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('rendez_vous', function (Blueprint $table) {
            if (!Schema::hasColumn('rendez_vous', 'planning_medecin_id')) {
                $table->uuid('planning_medecin_id')->nullable()->after('medecin_id');
                $table->foreign('planning_medecin_id')
                    ->references('id')
                    ->on('planning_medecins')
                    ->nullOnDelete();
            }
        });

        Schema::table('consultations', function (Blueprint $table) {
            if (!Schema::hasColumn('consultations', 'statut')) {
                $table->string('statut', 20)->default('EN_COURS')->after('date_heure')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rendez_vous', function (Blueprint $table) {
            if (Schema::hasColumn('rendez_vous', 'planning_medecin_id')) {
                $table->dropForeign(['planning_medecin_id']);
                $table->dropColumn('planning_medecin_id');
            }
        });

        Schema::table('consultations', function (Blueprint $table) {
            if (Schema::hasColumn('consultations', 'statut')) {
                $table->dropColumn('statut');
            }
        });
    }
};
