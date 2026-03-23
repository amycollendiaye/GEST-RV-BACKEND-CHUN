<?php

namespace Database\Seeders;

use App\Models\PersonnelHopital;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonnelHopitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PersonnelHopital::factory()->count(20)->create();
    }
}
