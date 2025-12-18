<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdditionalUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find existing units or create if missing (defensive)
        $unitInfra = \App\Models\Unit::where('name', 'Infrastructure')->first();
        if (!$unitInfra) {
            $divIT = \App\Models\Division::firstOrCreate(['name' => 'IT Division']);
            $unitInfra = \App\Models\Unit::create(['name' => 'Infrastructure', 'division_id' => $divIT->id]);
        }

        $unitOpsA = \App\Models\Unit::where('name', 'Ops Area A')->first();
        if (!$unitOpsA) {
            $divOps = \App\Models\Division::firstOrCreate(['name' => 'Operations Division']);
            $unitOpsA = \App\Models\Unit::create(['name' => 'Ops Area A', 'division_id' => $divOps->id]);
        }

        // 1. Users for Infrastructure
        $userInfra = \App\Models\User::firstOrCreate(
            ['email' => 'unit_infra@example.com'],
            [
                'name' => 'Unit Infrastructure',
                'password' => bcrypt('password'),
                'role' => 'unit',
                'unit_id' => $unitInfra->id
            ]
        );

        $mgrInfra = \App\Models\User::firstOrCreate(
            ['email' => 'manager_infra@example.com'],
            [
                'name' => 'Manager Infrastructure',
                'password' => bcrypt('password'),
                'role' => 'manager',
                'unit_id' => $unitInfra->id
            ]
        );

        // 2. Users for Ops Area A
        $userOps = \App\Models\User::firstOrCreate(
            ['email' => 'unit_ops@example.com'],
            [
                'name' => 'Unit Ops A',
                'password' => bcrypt('password'),
                'role' => 'unit',
                'unit_id' => $unitOpsA->id
            ]
        );

        $mgrOps = \App\Models\User::firstOrCreate(
            ['email' => 'manager_ops@example.com'],
            [
                'name' => 'Manager Ops A',
                'password' => bcrypt('password'),
                'role' => 'manager',
                'unit_id' => $unitOpsA->id
            ]
        );

        // 3. Create requests
        // Request 1: Infra - Submitted
        $req1 = \App\Models\ProcurementRequest::create([
            'user_id' => $userInfra->id,
            'unit_id' => $unitInfra->id,
            'status' => 'submitted',
            'manager_nominal' => 5000000,
        ]);
        $req1->items()->create([
            'name' => 'Server Rack', 'specification' => '42U', 'quantity' => 1, 'unit' => 'pc', 'budget_info' => 'Capex'
        ]);

        // Request 2: Ops - Approved by Manager
        $req2 = \App\Models\ProcurementRequest::create([
            'user_id' => $userOps->id,
            'unit_id' => $unitOpsA->id,
            'status' => 'approved_by_manager',
            'manager_nominal' => 1200000,
        ]);
        $req2->items()->create([
            'name' => 'Safety Boots', 'specification' => 'Size 42', 'quantity' => 10, 'unit' => 'pair', 'budget_info' => 'Opex'
        ]);

        // Request 3: Infra - Draft (or just another submitted for variety)
        $req3 = \App\Models\ProcurementRequest::create([
            'user_id' => $userInfra->id,
            'unit_id' => $unitInfra->id,
            'status' => 'submitted',
            'manager_nominal' => 750000,
        ]);
        $req3->items()->create([
            'name' => 'LAN Cables', 'specification' => 'Cat 6 305m', 'quantity' => 2, 'unit' => 'roll', 'budget_info' => 'Opex'
        ]);
    }
}
