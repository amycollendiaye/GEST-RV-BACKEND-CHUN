<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personel_hopitals', function (Blueprint $table) {
            if (Schema::hasColumn('personel_hopitals', 'login')) {
                $table->dropUnique(['login']);
                $table->dropColumn('login');
            }
            if (Schema::hasColumn('personel_hopitals', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('personel_hopitals', 'first_login')) {
                $table->dropColumn('first_login');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personel_hopitals', function (Blueprint $table) {
            if (!Schema::hasColumn('personel_hopitals', 'login')) {
                $table->string('login')->unique()->after('matricule');
            }
            if (!Schema::hasColumn('personel_hopitals', 'password')) {
                $table->string('password')->after('login');
            }
            if (!Schema::hasColumn('personel_hopitals', 'first_login')) {
                $table->boolean('first_login')->default(true)->after('password');
            }
        });
    }
};
