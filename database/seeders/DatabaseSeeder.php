<?php

namespace Database\Seeders;

use App\Models\PersonnelHopital;
use App\Models\ServiceMedical;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ServiceMedical::factory()->count(10)->create();
        PersonnelHopital::factory()->count(10)->create();
    }
}
