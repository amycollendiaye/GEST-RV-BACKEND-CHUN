<?php

use App\Models\PersonelHopital;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personel_hopitals', function (Blueprint $table) {
            $table->string('email_hash')->nullable()->after('email');
            $table->string('telephone_hash')->nullable()->after('telephone');
        });

        PersonelHopital::withTrashed()->orderBy('id')->chunk(100, function ($personnels) {
            foreach ($personnels as $personnel) {
                $email = $personnel->email;
                $telephone = $personnel->telephone;

                $emailHash = $email ? hash('sha256', strtolower(trim($email))) : null;
                $telephoneHash = $telephone
                    ? hash('sha256', preg_replace('/\\D+/', '', $telephone))
                    : null;

                DB::table('personel_hopitals')
                    ->where('id', $personnel->id)
                    ->update([
                        'email_hash' => $emailHash,
                        'telephone_hash' => $telephoneHash,
                    ]);
            }
        });

        Schema::table('personel_hopitals', function (Blueprint $table) {
            $table->string('email_hash')->nullable(false)->change();
            $table->string('telephone_hash')->nullable(false)->change();
        });

        // Unique only for active rows (soft-deletes ignored).
        DB::statement(
            'create unique index personel_hopitals_email_hash_unique ' .
            'on personel_hopitals(email_hash) where deleted_at is null'
        );
        DB::statement(
            'create unique index personel_hopitals_telephone_hash_unique ' .
            'on personel_hopitals(telephone_hash) where deleted_at is null'
        );
    }

    public function down(): void
    {
        Schema::table('personel_hopitals', function (Blueprint $table) {
            $table->dropColumn('email_hash');
            $table->dropColumn('telephone_hash');
        });

        DB::statement('drop index if exists personel_hopitals_email_hash_unique');
        DB::statement('drop index if exists personel_hopitals_telephone_hash_unique');
    }
};
