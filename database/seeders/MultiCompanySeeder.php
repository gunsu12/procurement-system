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
        // Disable activity logging for seeder if it exists
        config(['activitylog.enabled' => false]);

        echo "Starting seeder...\n";

        // Clear existing data to avoid conflicts
        echo "Clearing database...\n";
        DB::table('request_logs')->delete();
        DB::table('procurement_items')->delete();
        DB::table('procurement_requests')->delete();
        DB::table('users')->delete();
        DB::table('units')->delete();
        DB::table('divisions')->delete();
        DB::table('companies')->delete();
        echo "Database cleared.\n";

        // 1. Create Companies
        echo "Creating companies...\n";
        $rsuId = DB::table('companies')->insertGetId(['name' => 'RSU Bali Royal', 'code' => 'BROS', 'is_holding' => false, 'created_at' => now(), 'updated_at' => now()]);
        $rsiaId = DB::table('companies')->insertGetId(['name' => 'RSIA Bali Royal', 'code' => 'BIRO', 'is_holding' => false, 'created_at' => now(), 'updated_at' => now()]);
        $holdingId = DB::table('companies')->insertGetId(['name' => 'PT. Putra Husada Jaya', 'code' => 'PHJ', 'is_holding' => true, 'created_at' => now(), 'updated_at' => now()]);
        echo "Companies created.\n";

        // 2. Setup Company Branches
        $this->setupCompanyBranch($rsuId, 'BROS');
        $this->setupCompanyBranch($rsiaId, 'BIRO');

        // 3. Setup Holding Users
        $this->setupHoldingUsers($holdingId);

        // 4. Create Master Super Admin
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'company_id' => $holdingId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Seeding completed successfully!\n";
    }

    private function setupCompanyBranch($companyId, $companyCode)
    {
        echo "Setting up {$companyCode}...\n";

        // Create Divisions
        $divMedicalId = DB::table('divisions')->insertGetId(['name' => 'Medical Services', 'company_id' => $companyId, 'created_at' => now(), 'updated_at' => now()]);
        $divGeneralId = DB::table('divisions')->insertGetId(['name' => 'General Affairs', 'company_id' => $companyId, 'created_at' => now(), 'updated_at' => now()]);

        // Create Units
        $unitERId = DB::table('units')->insertGetId(['name' => 'Emergency Unit', 'division_id' => $divMedicalId, 'company_id' => $companyId, 'created_at' => now(), 'updated_at' => now()]);
        $unitITId = DB::table('units')->insertGetId(['name' => 'IT Department', 'division_id' => $divGeneralId, 'company_id' => $companyId, 'created_at' => now(), 'updated_at' => now()]);

        // Create Users
        $roles = [
            'unit' => 'Staff',
            'manager' => 'Manager',
            'budgeting' => 'Budgeting',
            'director_company' => 'Director',
        ];

        $userIds = [];
        foreach ($roles as $role => $label) {
            $unitId = ($role == 'unit' || $role == 'manager') ? $unitERId : null;
            $userIds[$role] = DB::table('users')->insertGetId([
                'name' => $label . ' ' . $companyCode,
                'email' => strtolower($role) . '@' . strtolower($companyCode) . '.com',
                'username' => strtolower($role) . '_' . strtolower($companyCode),
                'password' => bcrypt('password'),
                'role' => $role,
                'company_id' => $companyId,
                'unit_id' => $unitId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Create Sample Requests
        $this->createRequest($userIds['unit'], $unitERId, $companyId, 'submitted', 'Office Supplies for ' . $companyCode);
    }

    private function setupHoldingUsers($companyId)
    {
        echo "Setting up Holding Users...\n";
        $holdingRoles = [
            'finance_manager_holding' => 'Finance Manager Holding',
            'finance_director_holding' => 'Finance Director Holding',
            'general_director_holding' => 'General Director Holding',
            'purchasing' => 'Purchasing Team',
        ];

        foreach ($holdingRoles as $role => $name) {
            DB::table('users')->insert([
                'name' => $name,
                'email' => str_replace('_', '', $role) . '@phj.com',
                'username' => str_replace('_', '', $role),
                'password' => bcrypt('password'),
                'role' => $role,
                'company_id' => $companyId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function createRequest($userId, $unitId, $companyId, $status, $title)
    {
        $prefix = 'PRC/' . date('Ymd') . '/';
        $prId = DB::table('procurement_requests')->insertGetId([
            'code' => $prefix . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => $userId,
            'unit_id' => $unitId,
            'company_id' => $companyId,
            'status' => $status,
            'notes' => $title,
            'request_type' => 'nonaset',
            'is_medical' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('procurement_items')->insert([
            'procurement_request_id' => $prId,
            'name' => 'General Item 1',
            'quantity' => 10,
            'estimated_price' => 50000,
            'unit' => 'Pcs',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('request_logs')->insert([
            'procurement_request_id' => $prId,
            'user_id' => $userId,
            'action' => 'submitted',
            'note' => 'Initial submission',
            'status_before' => 'draft',
            'status_after' => 'submitted',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function addLog($prId, $userId, $action, $note, $before, $after)
    {
        DB::table('request_logs')->insert([
            'procurement_request_id' => $prId,
            'user_id' => $userId,
            'action' => $action,
            'note' => $note,
            'status_before' => $before,
            'status_after' => $after,
        ]);
    }
}
