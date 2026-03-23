<?php

namespace Database\Seeders;

use App\Models\ServiceMedical;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceMedicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServiceMedical::factory()->count(10)->create();
    }
}
