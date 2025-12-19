<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Division;
use App\Models\Unit;
use App\Models\User;
use App\Models\ProcurementRequest;
use App\Models\ProcurementItem;
use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;

class MultiCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data to avoid conflicts (PostgreSQL way)
        DB::statement("SET session_replication_role = 'replica';");
        \App\Models\RequestLog::truncate();
        \App\Models\ProcurementItem::truncate();
        \App\Models\ProcurementRequest::truncate();
        \App\Models\User::truncate();
        \App\Models\Unit::truncate();
        \App\Models\Division::truncate();
        \App\Models\Company::truncate();
        DB::statement("SET session_replication_role = 'origin';");

        // 1. Create Companies
        $rsu = Company::create(['name' => 'RSU Bali Royal', 'code' => 'BROS', 'is_holding' => false]);
        $rsia = Company::create(['name' => 'RSIA Bali Royal', 'code' => 'BIRO', 'is_holding' => false]);
        $holding = Company::create(['name' => 'PT. Putra Husada Jaya', 'code' => 'PHJ', 'is_holding' => true]);

        // 2. Setup RSU Bali Royal (BROS)
        $this->setupCompanyBranch($rsu);

        // 3. Setup RSIA Bali Royal (BIRO)
        $this->setupCompanyBranch($rsia);

        // 4. Setup Holding Users (PHJ)
        $this->setupHoldingUsers($holding);

        // 5. Create Master Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'company_id' => $holding->id
        ]);

        echo "Seeding completed successfully!\n";
    }

    private function setupCompanyBranch($company)
    {
        // Create Divisions
        $divMedical = Division::create(['name' => 'Medical Services', 'company_id' => $company->id]);
        $divGeneral = Division::create(['name' => 'General Affairs', 'company_id' => $company->id]);

        // Create Units
        $unitER = Unit::create(['name' => 'Emergency Unit', 'division_id' => $divMedical->id, 'company_id' => $company->id]);
        $unitIT = Unit::create(['name' => 'IT Department', 'division_id' => $divGeneral->id, 'company_id' => $company->id]);

        // Create Users for branch roles
        $roles = [
            'unit' => 'Staff',
            'manager' => 'Manager',
            'budgeting' => 'Budgeting',
            'director_company' => 'Director',
        ];

        $users = [];
        foreach ($roles as $role => $label) {
            $unitId = ($role == 'unit' || $role == 'manager') ? $unitER->id : null;
            $users[$role] = User::create([
                'name' => $label . ' ' . $company->code,
                'email' => strtolower($role) . '@' . strtolower($company->code) . '.com',
                'password' => bcrypt('password'),
                'role' => $role,
                'company_id' => $company->id,
                'unit_id' => $unitId
            ]);
        }

        // Create Sample Requests at different stages

        // Request 1: Submitted (Waiting for Manager)
        $this->createRequest($users['unit'], $unitER, $company, 'submitted', 'Office Supplies for ' . $company->code);

        // Request 2: Approved by Manager (Waiting for Budgeting)
        $pr2 = $this->createRequest($users['unit'], $unitER, $company, 'approved_by_manager', 'Medical Equipment for ' . $company->code);
        $this->addLog($pr2, $users['manager'], 'approved', 'Checking stock and availability', 'submitted', 'approved_by_manager');

        // Request 3: Approved by Budgeting (Waiting for Company Director)
        $pr3 = $this->createRequest($users['unit'], $unitER, $company, 'approved_by_budgeting', 'IT Infrastructure for ' . $company->code);
        $this->addLog($pr3, $users['manager'], 'approved', 'Approved', 'submitted', 'approved_by_manager');
        $this->addLog($pr3, $users['budgeting'], 'approved', 'Budget available', 'approved_by_manager', 'approved_by_budgeting');

        // Request 4: Approved by Company Director (Waiting for Holding Finance Manager)
        $pr4 = $this->createRequest($users['unit'], $unitER, $company, 'approved_by_dir_company', 'Renovation Project for ' . $company->code);
        $this->addLog($pr4, $users['manager'], 'approved', 'Approved', 'submitted', 'approved_by_manager');
        $this->addLog($pr4, $users['budgeting'], 'approved', 'Budget confirmed', 'approved_by_manager', 'approved_by_budgeting');
        $this->addLog($pr4, $users['director_company'], 'approved', 'Proceed to holding approval', 'approved_by_budgeting', 'approved_by_dir_company');
    }

    private function setupHoldingUsers($company)
    {
        $holdingRoles = [
            'finance_manager_holding' => 'Finance Manager Holding',
            'finance_director_holding' => 'Finance Director Holding',
            'general_director_holding' => 'General Director Holding',
            'purchasing' => 'Purchasing Team',
        ];

        foreach ($holdingRoles as $role => $name) {
            User::create([
                'name' => $name,
                'email' => str_replace('_', '', $role) . '@phj.com',
                'password' => bcrypt('password'),
                'role' => $role,
                'company_id' => $company->id
            ]);
        }
    }

    private function createRequest($user, $unit, $company, $status, $title)
    {
        $pr = ProcurementRequest::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'company_id' => $company->id,
            'status' => $status,
            'notes' => $title,
            'request_type' => 'nonaset',
            'is_medical' => false,
        ]);

        ProcurementItem::create([
            'procurement_request_id' => $pr->id,
            'name' => 'General Item 1',
            'quantity' => 10,
            'estimated_price' => 50000,
            'unit' => 'Pcs'
        ]);

        $pr->logs()->create([
            'user_id' => $user->id,
            'action' => 'submitted',
            'note' => 'Initial submission',
            'status_before' => 'draft',
            'status_after' => 'submitted',
        ]);

        return $pr;
    }

    private function addLog($pr, $user, $action, $note, $before, $after)
    {
        $pr->logs()->create([
            'user_id' => $user->id,
            'action' => $action,
            'note' => $note,
            'status_before' => $before,
            'status_after' => $after,
        ]);
    }
}
