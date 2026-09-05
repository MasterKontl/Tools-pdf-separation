<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'kurniawanjr354@gmail.com');
        $adminPassword = env('ADMIN_PASSWORD', 'AdminSecure2026!');

        $unlimitedPlan = Plan::where('slug', 'unlimited')->first();

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'DKV Administrator',
                'password' => Hash::make($adminPassword),
                'role' => 'ADMIN',
                'plan_id' => $unlimitedPlan?->id,
                'daily_limit' => null,
                'unlimited' => true,
                'is_active' => true,
            ]
        );
    }
}
