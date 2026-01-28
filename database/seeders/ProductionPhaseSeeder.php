<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductionPhaseSeeder extends Seeder
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

        echo "Starting Production Phase Seeder...\n";

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

        // 1. Create Holding Company
        echo "Creating holding company...\n";
        $company = Company::firstOrCreate(
            ['code' => 'PT'],
            [
                'name' => 'PT. Putra Husada Jaya',
                'is_holding' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        echo "Company created: {$company->name} (ID: {$company->id})\n";

        // 2. Create Super Admin User
        echo "Creating super admin user...\n";
        $user = User::firstOrCreate(
            ['email' => 'it@baliroyalhospital.co.id'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
                'company_id' => $company->id,
                'is_first_login' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        echo "User created: {$user->name} ({$user->email})\n";

        echo "Production Phase Seeding completed successfully!\n";
        echo "\n";
        echo "=== Login Credentials ===\n";
        echo "Email: it@baliroyalhospital.co.id\n";
        echo "Password: password\n";
        echo "First Login: true\n";
        echo "========================\n";
    }
}
