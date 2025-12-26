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
        echo "Starting Purchasing Phase Seeder (Optimized for Outstanding Report)...\n";

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

        // Create 20 transactions to have variety
        for ($i = 1; $i <= 20; $i++) {
            echo "Creating transaction $i/20...\n";

            // Randomly select a company
            $company = $companies->random();
            $unit = Unit::where('company_id', $company->id)->inRandomOrder()->first();
            $unitUser = User::where('role', 'unit')
                ->where('company_id', $company->id)
                ->first();

            if (!$unit || !$unitUser) {
                echo "Skipping transaction $i: Missing unit or user for company {$company->name}\n";
                continue;
            }

            // Get approval chain users
            $manager = User::where('role', 'manager')->where('company_id', $company->id)->first();
            $budgeting = User::where('role', 'budgeting')->where('company_id', $company->id)->first();
            $dirCompany = User::where('role', 'director_company')->where('company_id', $company->id)->first();
            $financeManager = User::where('role', 'finance_manager_holding')->first();
            $financeDirector = User::where('role', 'finance_director_holding')->first();
            $generalDirector = User::where('role', 'general_director_holding')->first();

            $isCito = rand(0, 100) < 20; // 20% Cito
            $requestType = $requestTypes[array_rand($requestTypes)];
            $isMedical = rand(0, 1) === 1;

            // Varied date for "Outstanding" testing
            // 1-5: Over 14 days ago
            // 6-10: 8-14 days ago
            // 11-20: 1-7 days ago
            if ($i <= 5) {
                $baseDate = now()->subDays(rand(15, 30));
            } elseif ($i <= 10) {
                $baseDate = now()->subDays(rand(8, 14));
            } else {
                $baseDate = now()->subDays(rand(1, 7));
            }

            $procurementRequest = ProcurementRequest::create([
                'user_id' => $unitUser->id,
                'unit_id' => $unit->id,
                'company_id' => $company->id,
                'status' => 'processing',
                'notes' => "Purchasing Phase Test Transaction #$i - {$company->name}",
                'request_type' => $requestType,
                'is_medical' => $isMedical,
                'is_cito' => $isCito,
                'cito_reason' => $isCito ? $citoReasons[array_rand($citoReasons)] : null,
                'created_at' => (clone $baseDate)->subDays(2), // Submitted 2 days before processing
            ]);

            // Create 5-15 items for this transaction
            $itemCount = rand(5, 15);
            for ($j = 1; $j <= $itemCount; $j++) {
                $item = $sampleItems[array_rand($sampleItems)];
                $quantity = rand(1, 20);
                $price = rand($item['price_range'][0], $item['price_range'][1]);

                // Randomly mark some items as checked (already purchased)
                // This is to test if the report correctly excludes checked items
                $isChecked = rand(0, 100) < 30; // 30% chance an item is already checked

                ProcurementItem::create([
                    'procurement_request_id' => $procurementRequest->id,
                    'name' => $item['name'],
                    'specification' => $item['specification'],
                    'quantity' => $quantity,
                    'estimated_price' => $price,
                    'unit' => $item['unit'],
                    'budget_info' => 'Budget ' . date('Y') . ' - Item ' . $j,
                    'is_checked' => $isChecked,
                    'checked_at' => $isChecked ? (clone $baseDate)->addHours(rand(1, 5)) : null,
                    'checked_by' => $isChecked ? User::where('role', 'purchasing')->first()->id : null,
                ]);
            }

            // Find purchasing user
            $purchasingUser = User::where('role', 'purchasing')->where('company_id', $company->id)->first()
                ?? User::where('role', 'purchasing')->first();

            // Add logs with the baseDate for 'processing' status
            $logSequence = [
                ['status' => 'submitted', 'days_before' => 2, 'user' => $unitUser],
                ['status' => 'approved_by_manager', 'days_before' => 1.8, 'user' => $manager],
                ['status' => 'approved_by_budgeting', 'days_before' => 1.5, 'user' => $budgeting],
                ['status' => 'approved_by_dir_company', 'days_before' => 1.2, 'user' => $dirCompany],
                ['status' => 'approved_finance_manager', 'days_before' => 0.8, 'user' => $financeManager],
                ['status' => 'approved_finance_director', 'days_before' => 0.5, 'user' => $financeDirector],
                ['status' => 'approved_general_director', 'days_before' => 0.2, 'user' => $generalDirector],
                ['status' => 'processing', 'days_before' => 0, 'user' => $purchasingUser],
            ];

            foreach ($logSequence as $seq) {
                if ($seq['user']) {
                    RequestLog::create([
                        'procurement_request_id' => $procurementRequest->id,
                        'user_id' => $seq['user']->id,
                        'action' => $seq['status'] === 'submitted' ? 'submitted' : ($seq['status'] === 'processing' ? 'purchasing' : 'approved'),
                        'note' => 'Automatic seeder log for ' . $seq['status'],
                        'status_before' => 'previous_status', // Simplified
                        'status_after' => $seq['status'],
                        'created_at' => (clone $baseDate)->subHours($seq['days_before'] * 24),
                    ]);
                }
            }
        }

        echo "Purchasing Phase Seeder completed successfully!\n";
        echo "Created 20 transactions with varied outstanding ages and checked statuses.\n";
    }

}
