<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Division;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncHrsMasterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrs:sync-master-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Master Data (Units, Divisions, Companies) from HRS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $endpoint = config('services.hrs.endpoint');
        $token = config('services.hrs.token');

        if (!$endpoint || !$token) {
            $this->error('HRS Endpoint or Token not configured.');
            return 1;
        }

        $this->info("Fetching data from: {$endpoint}");

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $token,
            ])->get($endpoint);

            if ($response->failed()) {
                $this->error("Failed to fetch data. Status: " . $response->status());
                $this->error($response->body());
                return 1;
            }

            $data = $response->json();

            if (!is_array($data)) {
                $this->error("Invalid response format. Expected JSON array.");
                return 1;
            }

            $this->info("Fetched " . count($data) . " records. Starting sync...");

            foreach ($data as $item) {
                // 1. Sync Company
                // Mapping: comp_code -> code, comp_name -> name
                $company = Company::updateOrCreate(
                    ['code' => $item['comp_code']],
                    ['name' => $item['comp_name'] ?? $item['comp_code']]
                );

                // 2. Sync Division
                // Mapping: dvs_kode -> code, dvs_name -> name, company_id
                $division = Division::updateOrCreate(
                    [
                        'code' => $item['dvs_kode'],
                        'company_id' => $company->id
                    ],
                    ['name' => $item['dvs_name']]
                );

                // 3. Sync Unit
                // Mapping: unt_kode -> code, unt_name -> name, division_id, company_id
                Unit::updateOrCreate(
                    [
                        'code' => $item['unt_kode'],
                        'division_id' => $division->id,
                        'company_id' => $company->id
                    ],
                    ['name' => $item['unt_name']]
                );
            }

            $this->info("Master data sync completed successfully.");
            return 0;

        } catch (\Exception $e) {
            $this->error("An error occurred during sync: " . $e->getMessage());
            Log::error("HRS Sync Error: " . $e->getMessage());
            return 1;
        }
    }
}
