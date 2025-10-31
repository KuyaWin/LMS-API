<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
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
                'name' => 'Wash & Fold',
                'description' => 'Professional washing and folding service',
                'price' => 50.00,
                'unit' => 'kg',
                'icon' => '👕',
                'is_active' => true,
            ],
            [
                'name' => 'Dry Cleaning',
                'description' => 'Expert dry cleaning for delicate items',
                'price' => 100.00,
                'unit' => 'item',
                'icon' => '🧥',
                'is_active' => true,
            ],
            [
                'name' => 'Wash & Iron',
                'description' => 'Complete wash and ironing service',
                'price' => 75.00,
                'unit' => 'kg',
                'icon' => '👔',
                'is_active' => true,
            ],
            [
                'name' => 'Iron Only',
                'description' => 'Professional ironing service',
                'price' => 40.00,
                'unit' => 'kg',
                'icon' => '🔥',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}

