<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use App\Models\ProcurementRequest;
use App\Models\ProcurementItem;
use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;

class PurchasingPhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Starting Purchasing Phase Seeder...\n";

        // Get existing data
        $companies = Company::where('is_holding', false)->get();

        // Define sample items for procurement
        $sampleItems = [
            ['name' => 'Laptop Dell XPS 15', 'specification' => 'Intel i7, 16GB RAM, 512GB SSD', 'unit' => 'Pcs', 'price_range' => [15000000, 20000000]],
            ['name' => 'Office Chair Ergonomic', 'specification' => 'Adjustable height, lumbar support', 'unit' => 'Pcs', 'price_range' => [2000000, 3500000]],
            ['name' => 'Monitor LED 27"', 'specification' => '4K Resolution, IPS Panel', 'unit' => 'Pcs', 'price_range' => [4000000, 6000000]],
            ['name' => 'Printer HP LaserJet', 'specification' => 'Network ready, Duplex printing', 'unit' => 'Pcs', 'price_range' => [5000000, 8000000]],
            ['name' => 'Medical Gloves', 'specification' => 'Latex-free, Size M', 'unit' => 'Box', 'price_range' => [150000, 300000]],
            ['name' => 'Surgical Mask 3-ply', 'specification' => 'Disposable, certified', 'unit' => 'Box', 'price_range' => [100000, 200000]],
            ['name' => 'Hand Sanitizer 5L', 'specification' => '70% Alcohol content', 'unit' => 'Gallon', 'price_range' => [200000, 350000]],
            ['name' => 'Thermometer Digital', 'specification' => 'Non-contact infrared', 'unit' => 'Pcs', 'price_range' => [500000, 800000]],
            ['name' => 'Stethoscope Littmann', 'specification' => 'Cardiology IV model', 'unit' => 'Pcs', 'price_range' => [3000000, 4500000]],
            ['name' => 'Blood Pressure Monitor', 'specification' => 'Digital automatic', 'unit' => 'Pcs', 'price_range' => [800000, 1500000]],
            ['name' => 'ECG Paper Roll', 'specification' => '80mm x 20m', 'unit' => 'Roll', 'price_range' => [50000, 100000]],
            ['name' => 'Wheelchair Standard', 'specification' => 'Folding, steel frame', 'unit' => 'Pcs', 'price_range' => [1500000, 2500000]],
            ['name' => 'IV Stand Mobile', 'specification' => '4-hook, stainless steel', 'unit' => 'Pcs', 'price_range' => [500000, 800000]],
            ['name' => 'Hospital Bed Manual', 'specification' => '3-crank, with mattress', 'unit' => 'Pcs', 'price_range' => [5000000, 8000000]],
            ['name' => 'Defibrillator Portable', 'specification' => 'Automated External (AED)', 'unit' => 'Pcs', 'price_range' => [20000000, 30000000]],
            ['name' => 'Oxygen Cylinder', 'specification' => '6 cubic meter capacity', 'unit' => 'Pcs', 'price_range' => [2000000, 3000000]],
            ['name' => 'Nebulizer Compressor', 'specification' => 'For respiratory therapy', 'unit' => 'Pcs', 'price_range' => [800000, 1200000]],
            ['name' => 'Suction Pump Electric', 'specification' => 'Medical grade, portable', 'unit' => 'Pcs', 'price_range' => [3000000, 5000000]],
            ['name' => 'Infusion Pump', 'specification' => 'Programmable rate control', 'unit' => 'Pcs', 'price_range' => [8000000, 12000000]],
            ['name' => 'Autoclave Sterilizer', 'specification' => '50L capacity, digital', 'unit' => 'Pcs', 'price_range' => [15000000, 25000000]],
        ];

        $requestTypes = ['nonaset', 'aset'];
        $citoReasons = [
            'Urgent patient care requirement',
            'Equipment breakdown emergency',
            'Critical stock depletion',
            'Emergency preparedness',
            null
        ];

        // Create 10 transactions in purchasing phase
        for ($i = 1; $i <= 10; $i++) {
            echo "Creating transaction $i/10...\n";

            // Randomly select a company
            $company = $companies->random();
            $unit = Unit::where('company_id', $company->id)->first();
            $unitUser = User::where('role', 'unit')
                ->where('company_id', $company->id)
                ->first();

            if (!$unit || !$unitUser) {
                echo "Skipping transaction $i: Missing unit or user for company {$company->name}\n";
                continue;
            }

            // Get approval chain users
            $manager = User::where('role', 'manager')
                ->where('company_id', $company->id)
                ->first();
            $budgeting = User::where('role', 'budgeting')
                ->where('company_id', $company->id)
                ->first();
            $dirCompany = User::where('role', 'director_company')
                ->where('company_id', $company->id)
                ->first();
            $financeManager = User::where('role', 'finance_manager_holding')->first();
            $financeDirector = User::where('role', 'finance_director_holding')->first();
            $generalDirector = User::where('role', 'general_director_holding')->first();

            // Create procurement request in purchasing phase
            $isCito = rand(0, 1) === 1;
            $requestType = $requestTypes[array_rand($requestTypes)];

            $procurementRequest = ProcurementRequest::create([
                'user_id' => $unitUser->id,
                'unit_id' => $unit->id,
                'company_id' => $company->id,
                'status' => 'processing',
                'notes' => "Purchasing Phase Test Transaction #$i - {$company->name}",
                'request_type' => $requestType,
                'is_medical' => rand(0, 1) === 1,
                'is_cito' => $isCito,
                'cito_reason' => $isCito ? $citoReasons[array_rand($citoReasons)] : null,
            ]);

            // Create 10 items for this transaction
            for ($j = 1; $j <= 10; $j++) {
                $item = $sampleItems[array_rand($sampleItems)];
                $quantity = rand(1, 20);
                $price = rand($item['price_range'][0], $item['price_range'][1]);

                ProcurementItem::create([
                    'procurement_request_id' => $procurementRequest->id,
                    'name' => $item['name'],
                    'specification' => $item['specification'],
                    'quantity' => $quantity,
                    'estimated_price' => $price,
                    'unit' => $item['unit'],
                    'budget_info' => 'Budget ' . date('Y') . ' - Item ' . $j,
                    'is_checked' => false, // Initially not checked
                ]);
            }

            // Find purchasing user for this company
            $purchasingUser = User::where('role', 'purchasing')
                ->where('company_id', $company->id)
                ->first();

            if (!$purchasingUser) {
                // Should not happen if BranchPurchasingSeeder is run, but fallback
                $purchasingUser = User::where('role', 'purchasing')->first();
            }

            // Add complete approval history logs
            $logs = [
                [
                    'user' => $unitUser,
                    'action' => 'submitted',
                    'note' => 'Initial submission for procurement',
                    'status_before' => 'draft',
                    'status_after' => 'submitted',
                ],
                [
                    'user' => $manager,
                    'action' => 'approved',
                    'note' => 'Approved - Items are necessary for unit operations',
                    'status_before' => 'submitted',
                    'status_after' => 'approved_by_manager',
                ],
                [
                    'user' => $budgeting,
                    'action' => 'approved',
                    'note' => 'Budget verified and available',
                    'status_before' => 'approved_by_manager',
                    'status_after' => 'approved_by_budgeting',
                ],
                [
                    'user' => $dirCompany,
                    'action' => 'approved',
                    'note' => 'Company director approval granted',
                    'status_before' => 'approved_by_budgeting',
                    'status_after' => 'approved_by_dir_company',
                ],
                [
                    'user' => $financeManager,
                    'action' => 'approved',
                    'note' => 'Finance manager holding approval',
                    'status_before' => 'approved_by_dir_company',
                    'status_after' => 'approved_finance_manager',
                ],
                [
                    'user' => $financeDirector,
                    'action' => 'approved',
                    'note' => 'Finance director holding approval',
                    'status_before' => 'approved_finance_manager',
                    'status_after' => 'approved_finance_director',
                ],
                [
                    'user' => $generalDirector,
                    'action' => 'approved',
                    'note' => 'General director final approval',
                    'status_before' => 'approved_finance_director',
                    'status_after' => 'approved_general_director',
                ],
                [
                    'user' => $purchasingUser,
                    'action' => 'purchasing',
                    'note' => 'Moved to purchasing phase for item procurement',
                    'status_before' => 'approved_general_director',
                    'status_after' => 'processing',
                ],
            ];

            foreach ($logs as $logData) {
                if ($logData['user']) {
                    RequestLog::create([
                        'procurement_request_id' => $procurementRequest->id,
                        'user_id' => $logData['user']->id,
                        'action' => $logData['action'],
                        'note' => $logData['note'],
                        'status_before' => $logData['status_before'],
                        'status_after' => $logData['status_after'],
                        'created_at' => now()->subDays(10 - $i)->subHours(rand(1, 23)),
                    ]);
                }
            }
        }

        echo "Purchasing Phase Seeder completed successfully!\n";
        echo "Created 10 transactions with 10 items each (100 total items) in purchasing phase.\n";
    }
}
