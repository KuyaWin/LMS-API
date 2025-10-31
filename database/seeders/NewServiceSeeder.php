<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'name' => 'Wash Only - No Soap',
                'description' => 'Washing service without soap and fabric conditioner',
                'price' => 75.00,
                'unit' => 'kg',
                'icon' => '💧',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Wash Only - With Soap and Fabric Conditioner',
                'description' => 'Washing service with soap and fabric conditioner included',
                'price' => 100.00,
                'unit' => 'kg',
                'icon' => '🧼',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dry Only',
                'description' => 'Drying service only',
                'price' => 75.00,
                'unit' => 'kg',
                'icon' => '🌬️',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Wash & Dry - Without Soap and Fabric Conditioner',
                'description' => 'Complete wash and dry service without soap and fabric conditioner',
                'price' => 140.00,
                'unit' => 'kg',
                'icon' => '🔄',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Wash & Dry - With Soap and Fabric Conditioner',
                'description' => 'Complete wash and dry service with soap and fabric conditioner',
                'price' => 170.00,
                'unit' => 'kg',
                'icon' => '✨',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Full Service - Wash & Dry with Soap, Fold and Fabric Conditioner',
                'description' => 'Complete laundry service: wash, dry, fold with soap and fabric conditioner',
                'price' => 200.00,
                'unit' => 'kg',
                'icon' => '👕',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Comforter Wash & Dry - With Soap and Fabric Conditioner',
                'description' => 'Special comforter washing and drying service with soap and fabric conditioner',
                'price' => 200.00,
                'unit' => 'kg',
                'icon' => '🛏️',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('services')->insert($services);
        
        $this->command->info('Successfully seeded ' . count($services) . ' new services!');
    }
}
