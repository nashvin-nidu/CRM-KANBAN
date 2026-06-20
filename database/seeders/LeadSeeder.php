<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = [
            [
                'id' => 1,
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
                'company' => 'Acme Corp',
                'status' => 'New',
                'value' => 12500,
                'source' => 'Website',
                'date' => now()->subDays(15)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 2,
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'company' => 'Infinite Loop',
                'status' => 'Contacted',
                'value' => 45000,
                'source' => 'Referral',
                'date' => now()->subDays(2)->format('Y-m-d'),
                'rating' => 'cold',
            ],
            [
                'id' => 3,
                'name' => 'Charlie Brown',
                'email' => 'charlie@example.com',
                'company' => 'Peanuts Inc',
                'status' => 'Qualified',
                'value' => 8000,
                'source' => 'LinkedIn',
                'date' => now()->subDays(1)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 4,
                'name' => 'Diana Prince',
                'email' => 'diana@example.com',
                'company' => 'Wayne Ent.',
                'status' => 'Proposal Sent',
                'value' => 95000,
                'source' => 'Direct',
                'date' => now()->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 5,
                'name' => 'Ethan Hunt',
                'email' => 'ethan@example.com',
                'company' => 'IMF Agency',
                'status' => 'Won',
                'value' => 150000,
                'source' => 'Referral',
                'date' => now()->subMonths(2)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 6,
                'name' => 'Fiona Gallagher',
                'email' => 'fiona@example.com',
                'company' => "Patsy's Pies",
                'status' => 'Lost',
                'value' => 3200,
                'source' => 'Cold Call',
                'date' => now()->subYear()->format('Y-m-d'),
                'rating' => 'cold',
            ],
            [
                'id' => 7,
                'name' => 'George Clark',
                'email' => 'george@example.com',
                'company' => 'Nexus Ltd',
                'status' => 'New',
                'value' => 18000,
                'source' => 'Website',
                'date' => now()->subDays(4)->format('Y-m-d'),
                'rating' => 'cold',
            ],
            [
                'id' => 8,
                'name' => 'Hannah Abbott',
                'email' => 'hannah@example.com',
                'company' => 'Apothecary Co',
                'status' => 'Contacted',
                'value' => 5200,
                'source' => 'Inbound',
                'date' => now()->subDays(3)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 9,
                'name' => 'Ian Malcolm',
                'email' => 'ian@example.com',
                'company' => 'InGen Bios',
                'status' => 'Qualified',
                'value' => 65000,
                'source' => 'Conference',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 10,
                'name' => 'Julia Roberts',
                'email' => 'julia@example.com',
                'company' => 'Pretty Pics',
                'status' => 'Proposal Sent',
                'value' => 120000,
                'source' => 'Referral',
                'date' => now()->subDays(6)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 11,
                'name' => 'Kevin Bacon',
                'email' => 'kevin@example.com',
                'company' => 'Six Degrees',
                'status' => 'Won',
                'value' => 30000,
                'source' => 'Direct',
                'date' => now()->subDays(7)->format('Y-m-d'),
                'rating' => 'cold',
            ],
            [
                'id' => 12,
                'name' => 'Laura Croft',
                'email' => 'laura@example.com',
                'company' => 'Tomb Explorer',
                'status' => 'Lost',
                'value' => 45000,
                'source' => 'Cold Call',
                'date' => now()->subDays(8)->format('Y-m-d'),
                'rating' => 'cold',
            ],
            [
                'id' => 13,
                'name' => 'Bruce Wayne',
                'email' => 'bruce@example.com',
                'company' => 'Wayne Ent.',
                'status' => 'New',
                'value' => 500000,
                'source' => 'Direct',
                'date' => now()->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 14,
                'name' => 'Clark Kent',
                'email' => 'clark@example.com',
                'company' => 'Daily Planet',
                'status' => 'Contacted',
                'value' => 15000,
                'source' => 'Website',
                'date' => now()->subDays(1)->format('Y-m-d'),
                'rating' => 'cold',
            ],
            [
                'id' => 15,
                'name' => 'Peter Parker',
                'email' => 'peter@example.com',
                'company' => 'Daily Bugle',
                'status' => 'Qualified',
                'value' => 9000,
                'source' => 'LinkedIn',
                'date' => now()->subDays(2)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 16,
                'name' => 'Tony Stark',
                'email' => 'tony@example.com',
                'company' => 'Stark Ind.',
                'status' => 'Proposal Sent',
                'value' => 750000,
                'source' => 'Referral',
                'date' => now()->subDays(3)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 17,
                'name' => 'Natasha Romanoff',
                'email' => 'natasha@example.com',
                'company' => 'S.H.I.E.L.D.',
                'status' => 'Won',
                'value' => 110000,
                'source' => 'Inbound',
                'date' => now()->subDays(4)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 18,
                'name' => 'Steve Rogers',
                'email' => 'steve@example.com',
                'company' => 'Brooklyn Sp.',
                'status' => 'New',
                'value' => 25000,
                'source' => 'Conference',
                'date' => now()->subDays(10)->format('Y-m-d'),
                'rating' => 'cold',
            ],
            [
                'id' => 19,
                'name' => 'Wanda Maximoff',
                'email' => 'wanda@example.com',
                'company' => 'Westview Co',
                'status' => 'Qualified',
                'value' => 85000,
                'source' => 'Website',
                'date' => now()->subDays(12)->format('Y-m-d'),
                'rating' => 'warm',
            ],
            [
                'id' => 20,
                'name' => 'Barry Allen',
                'email' => 'barry@example.com',
                'company' => 'Star Labs',
                'status' => 'Won',
                'value' => 60000,
                'source' => 'Referral',
                'date' => now()->subDays(14)->format('Y-m-d'),
                'rating' => 'cold',
            ],
        ];

        $salesReps = User::where('role', 'sales_rep')->orderBy('id', 'asc')->get();

        foreach ($leads as $lead) {
            if ($salesReps->count() > 0) {
                $repIndex = $lead['id'] % 4;
                $lead['assigned_to'] = $salesReps[$repIndex]->id;
            }
            Lead::updateOrCreate(['id' => $lead['id']], $lead);
        }
    }
}
