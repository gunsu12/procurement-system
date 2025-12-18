<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create Divisions
        $divIT = \App\Models\Division::create(['name' => 'IT Division']);
        $divOps = \App\Models\Division::create(['name' => 'Operations Division']);

        // Create Units
        $unitDev = \App\Models\Unit::create(['name' => 'Development', 'division_id' => $divIT->id]);
        $unitInfra = \App\Models\Unit::create(['name' => 'Infrastructure', 'division_id' => $divIT->id]);
        $unitOpsA = \App\Models\Unit::create(['name' => 'Ops Area A', 'division_id' => $divOps->id]);

        // Create Users for each Role
        // 1. Unit
        \App\Models\User::create([
            'name' => 'Unit User', 'email' => 'unit@example.com', 'password' => bcrypt('password'),
            'role' => 'unit', 'unit_id' => $unitDev->id
        ]);
        // 2. Manager
        \App\Models\User::create([
            'name' => 'Manager User', 'email' => 'manager@example.com', 'password' => bcrypt('password'),
            'role' => 'manager', 'unit_id' => $unitDev->id // Manager of Dev unit
        ]);
        // 3. Budgeting
        \App\Models\User::create([
            'name' => 'Budgeting User', 'email' => 'budgeting@example.com', 'password' => bcrypt('password'),
            'role' => 'budgeting', 'unit_id' => $unitDev->id // Unit not critical for generic roles but required by DB
        ]);
        // 4. Director Company
        \App\Models\User::create([
            'name' => 'Director Company', 'email' => 'dircom@example.com', 'password' => bcrypt('password'),
            'role' => 'director_company', 'unit_id' => $unitDev->id
        ]);
        // 5. Finance Manager Holding
        \App\Models\User::create([
            'name' => 'Finance Mgr Holding', 'email' => 'finmgr@example.com', 'password' => bcrypt('password'),
            'role' => 'finance_manager_holding', 'unit_id' => $unitDev->id
        ]);
        // 6. Finance Director Holding
        \App\Models\User::create([
            'name' => 'Finance Dir Holding', 'email' => 'findir@example.com', 'password' => bcrypt('password'),
            'role' => 'finance_director_holding', 'unit_id' => $unitDev->id
        ]);
         // 7. General Director Holding
         \App\Models\User::create([
            'name' => 'General Dir Holding', 'email' => 'gendir@example.com', 'password' => bcrypt('password'),
            'role' => 'general_director_holding', 'unit_id' => $unitDev->id
        ]);
        // 8. Purchasing
        \App\Models\User::create([
            'name' => 'Purchasing User', 'email' => 'purchasing@example.com', 'password' => bcrypt('password'),
            'role' => 'purchasing', 'unit_id' => $unitDev->id
        ]);
    }
}
