<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class BranchPurchasingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Creating Branch Purchasing Users...\n";

        // Get branch companies (not holding)
        $branchCompanies = Company::where('is_holding', false)->get();

        foreach ($branchCompanies as $company) {
            $email = 'purchasing@' . strtolower($company->code) . '.com';

            // Check if user exists
            $user = User::where('email', $email)->first();

            if (!$user) {
                User::create([
                    'name' => 'Purchasing ' . $company->code,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'role' => 'purchasing',
                    'company_id' => $company->id
                ]);
                echo "Created purchasing user for {$company->name}: $email\n";
            } else {
                echo "Purchasing user already exists for {$company->name}: $email\n";
            }
        }
    }
}
