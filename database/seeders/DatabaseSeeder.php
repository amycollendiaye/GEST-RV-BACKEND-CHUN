<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\PersonnelHopital;
use App\Models\ServiceMedical;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

    ServiceMedical::factory()->count(10)->create();
    PersonnelHopital::factory()->count(10)->create();


    }
}
