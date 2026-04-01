<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('infos_connexions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('personel_hopital_id')->unique();
            $table->string('login')->unique();
            $table->string('password');
            $table->boolean('first_login')->default(true);
            $table->timestamps();

            $table->foreign('personel_hopital_id')
                ->references('id')
                ->on('personel_hopitals')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infos_connexions');
    }
};
