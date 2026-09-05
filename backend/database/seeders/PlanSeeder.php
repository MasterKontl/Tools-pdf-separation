<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Paket gratis untuk kebutuhan dasar mahasiswa dan desainer.',
                'daily_limit' => 3,
                'unlimited' => false,
                'price' => 0,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Paket harian 20 konversi untuk tugas kuliah dan eksplorasi karya.',
                'daily_limit' => 20,
                'unlimited' => false,
                'price' => 25000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Paket harian 100 konversi untuk studio grafis dan percetakan cepat.',
                'daily_limit' => 100,
                'unlimited' => false,
                'price' => 75000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'name' => 'Unlimited',
                'slug' => 'unlimited',
                'description' => 'Konversi tanpa batas kuota harian untuk produksi tanpa henti.',
                'daily_limit' => null,
                'unlimited' => true,
                'price' => 150000,
                'currency' => 'IDR',
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
