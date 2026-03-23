<?php

use App\Models\ServiceMedical;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_medicals', function (Blueprint $table) {
            $table->string('nom_hash')->nullable()->after('nom');
        });

        // Backfill hash for existing rows using decrypted values from the model
        ServiceMedical::withTrashed()->orderBy('id')->chunk(100, function ($services) {
            foreach ($services as $service) {
                $nom = $service->nom;
                if ($nom === null || trim($nom) === '') {
                    continue;
                }
                $hash = hash('sha256', strtolower(trim($nom)));
                DB::table('service_medicals')
                    ->where('id', $service->id)
                    ->update(['nom_hash' => $hash]);
            }
        });

        Schema::table('service_medicals', function (Blueprint $table) {
            $table->string('nom_hash')->nullable(false)->change();
            $table->unique('nom_hash', 'service_medicals_nom_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::table('service_medicals', function (Blueprint $table) {
            $table->dropUnique('service_medicals_nom_hash_unique');
            $table->dropColumn('nom_hash');
        });
    }
};
