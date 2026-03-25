<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_medecins', function (Blueprint $table) {
            if (!Schema::hasColumn('planning_medecins', 'service_medical_id')) {
                $table->uuid('service_medical_id')->nullable()->after('medecin_id');
            }

            if (!Schema::hasColumn('planning_medecins', 'heure_ouverture')) {
                $table->time('heure_ouverture')->nullable()->after('date');
            }

            if (!Schema::hasColumn('planning_medecins', 'heure_fermeture')) {
                $table->time('heure_fermeture')->nullable()->after('heure_ouverture');
            }

            if (!Schema::hasColumn('planning_medecins', 'capacite')) {
                $table->unsignedInteger('capacite')->nullable()->after('heure_fermeture');
            }

            if (!Schema::hasColumn('planning_medecins', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        DB::statement('update planning_medecins set heure_ouverture = coalesce(heure_ouverture, heure_debut)');
        DB::statement('update planning_medecins set heure_fermeture = coalesce(heure_fermeture, heure_fin)');
        DB::statement('update planning_medecins set capacite = coalesce(capacite, 1)');
        DB::statement('
            update planning_medecins pm
            set service_medical_id = ph.service_medical_id
            from personel_hopitals ph
            where pm.medecin_id = ph.id
              and pm.service_medical_id is null
        ');

        Schema::table('planning_medecins', function (Blueprint $table) {
            $table->uuid('service_medical_id')->nullable(false)->change();
            $table->time('heure_ouverture')->nullable(false)->change();
            $table->time('heure_fermeture')->nullable(false)->change();
            $table->unsignedInteger('capacite')->nullable(false)->change();

            $table->foreign('service_medical_id')
                ->references('id')
                ->on('service_medicals')
                ->cascadeOnDelete();
        });

        DB::statement(
            'create unique index if not exists planning_medecins_unique_active_idx ' .
            'on planning_medecins(medecin_id, service_medical_id, date) where deleted_at is null'
        );
        DB::statement(
            'create index if not exists planning_medecins_service_date_idx ' .
            'on planning_medecins(service_medical_id, date)'
        );
    }

    public function down(): void
    {
        DB::statement('drop index if exists planning_medecins_unique_active_idx');
        DB::statement('drop index if exists planning_medecins_service_date_idx');

        Schema::table('planning_medecins', function (Blueprint $table) {
            if (Schema::hasColumn('planning_medecins', 'service_medical_id')) {
                $table->dropForeign(['service_medical_id']);
                $table->dropColumn('service_medical_id');
            }

            if (Schema::hasColumn('planning_medecins', 'heure_ouverture')) {
                $table->dropColumn('heure_ouverture');
            }

            if (Schema::hasColumn('planning_medecins', 'heure_fermeture')) {
                $table->dropColumn('heure_fermeture');
            }

            if (Schema::hasColumn('planning_medecins', 'capacite')) {
                $table->dropColumn('capacite');
            }

            if (Schema::hasColumn('planning_medecins', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
