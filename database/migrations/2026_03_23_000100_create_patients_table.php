<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nom');
            $table->string('prenom');
            $table->string('email');
            $table->string('email_hash')->nullable()->index();
            $table->string('telephone');
            $table->string('telephone_hash')->nullable()->index();
            $table->date('date_naissance');
            $table->string('adresse');

            $table->string('matricule')->unique();
            $table->string('login')->unique();
            $table->string('password');
            $table->boolean('first_login')->default(true);
            $table->enum('statut', ['ACTIF', 'INACTIF'])->default('ACTIF')->index();

            $table->uuid('activation_token')->nullable()->unique();
            $table->timestamp('activation_token_expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('create unique index patients_email_hash_unique on patients(email_hash) where deleted_at is null');
        DB::statement('create unique index patients_telephone_hash_unique on patients(telephone_hash) where deleted_at is null');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('drop index if exists patients_email_hash_unique');
        DB::statement('drop index if exists patients_telephone_hash_unique');
        Schema::dropIfExists('patients');
    }
};
