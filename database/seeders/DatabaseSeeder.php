<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin'),
            'role' => 'admin',
        ]);

        $salesReps = [
            [
                'name' => 'Sarah Jenkins',
                'email' => 'executive1@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'sales_rep',
            ],
            [
                'name' => 'Alex Rivera',
                'email' => 'executive2@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'sales_rep',
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'executive3@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'sales_rep',
            ],
            [
                'name' => 'Marcus Chen',
                'email' => 'executive4@gmail.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'sales_rep',
            ],
        ];

        foreach ($salesReps as $rep) {
            User::factory()->create($rep);
        }

        $this->call(LeadSeeder::class);
    }
}
