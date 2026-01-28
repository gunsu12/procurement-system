<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Http;
use App\Models\Company;
use App\Models\Division;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = User::with('unit', 'company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $users = $query->paginate(10)->withQueryString();
        $companies = \App\Models\Company::all();
        return view('users.index', compact('users', 'companies'));
    }

    public function create()
    {
        $units = Unit::all();
        $companies = \App\Models\Company::all();
        $roles = [
            'super_admin' => 'Super Admin',
            'unit' => 'Unit',
            'manager' => 'Manager',
            'budgeting' => 'Budgeting',
            'director_company' => 'Director Company',
            'finance_manager_holding' => 'Finance Manager Holding',
            'finance_director_holding' => 'Finance Director Holding',
            'general_director_holding' => 'General Director Holding',
            'purchasing' => 'Purchasing',
        ];
        return view('users.create', compact('units', 'roles', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'unit_id' => 'nullable|exists:units,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'unit_id' => $request->unit_id,
            'company_id' => $request->company_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $units = Unit::all();
        $companies = \App\Models\Company::all();
        $roles = [
            'super_admin' => 'Super Admin',
            'unit' => 'Unit',
            'manager' => 'Manager',
            'budgeting' => 'Budgeting',
            'director_company' => 'Director Company',
            'finance_manager_holding' => 'Finance Manager Holding',
            'finance_director_holding' => 'Finance Director Holding',
            'general_director_holding' => 'General Director Holding',
            'purchasing' => 'Purchasing',
        ];
        return view('users.edit', compact('user', 'units', 'roles', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string',
            'unit_id' => 'nullable|exists:units,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'unit_id' => $request->unit_id,
            'company_id' => $request->company_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    private function fetchHrsData()
    {
        $baseUrl = config('services.hrs.base_url', env('HRS_BASE_URL'));
        $apiKey = config('services.hrs.api_key', env('HRS_API_KEY'));

        \Log::info('HRS Sync: Attempting to fetch data', [
            'base_url' => $baseUrl,
            'api_key_present' => !empty($apiKey)
        ]);

        return Http::timeout(120)
            ->retry(3, 100) // Retry 3 times with 100ms delay
            ->withOptions([
                'verify' => false, // Temporarily disable SSL verification for testing
                'connect_timeout' => 30,
            ])
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get("{$baseUrl}/sync/employees");
    }

    public function previewSync()
    {
        try {
            \Log::info('HRS Sync: Preview sync started');

            $response = $this->fetchHrsData();

            if ($response->failed()) {
                $errorMessage = 'Failed to fetch data from HRS. Status: ' . $response->status();
                $errorBody = $response->body();

                \Log::error('HRS Sync: API request failed', [
                    'status' => $response->status(),
                    'body' => $errorBody
                ]);

                return response()->json([
                    'error' => $errorMessage,
                    'details' => $errorBody,
                    'status_code' => $response->status()
                ], 500);
            }

            $employees = $response->json();

            if (!is_array($employees)) {
                \Log::error('HRS Sync: Invalid data format', [
                    'response_type' => gettype($employees)
                ]);
                return response()->json(['error' => 'Invalid data format from HRS.'], 500);
            }

            \Log::info('HRS Sync: Successfully fetched employees', [
                'count' => count($employees)
            ]);

            return response()->json($employees);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('HRS Sync: Connection error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Connection error: Unable to reach HRS server',
                'details' => $e->getMessage()
            ], 500);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('HRS Sync: Request error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Request error occurred',
                'details' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            \Log::error('HRS Sync: Unexpected error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An unexpected error occurred',
                'details' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function sync(Request $request)
    {
        try {
            $response = $this->fetchHrsData();

            if ($response->failed()) {
                return redirect()->back()->with('error', 'Failed to fetch data from HRS: ' . $response->status());
            }

            $employees = $response->json();


            if (!is_array($employees)) {
                return redirect()->back()->with('error', 'Invalid data format from HRS.');
            }

            $counters = [
                'users_created' => 0,
                'users_updated' => 0,
            ];

            // Get selected NIKs
            $selectedNiks = $request->input('selected_niks', []);

            if (empty($selectedNiks)) {
                return redirect()->back()->with('error', 'No users selected for sync.');
            }

            // 2. Process each employee
            foreach ($employees as $emp) {
                // Skip if not in selected list
                if (!in_array($emp['nik'], $selectedNiks)) {
                    continue;
                }

                // Ensure Company (Site) exists
                // Ensure Company (Site) exists
                // Mapping: site_code (HRS) -> code (Company)
                // Note: Company model uses 'code' and 'name'.
                $company = Company::firstOrCreate(
                    ['code' => $emp['site_code']],
                    [
                        'name' => $emp['site_name'],
                        'is_holding' => false // Default assumption
                    ]
                );

                // Ensure Division exists
                // Mapping: division_code (HRS) -> code (Division)
                $division = Division::firstOrCreate(
                    [
                        'code' => $emp['division_code'],
                        'company_id' => $company->id
                    ],
                    [
                        'name' => $emp['division_name']
                    ]
                );

                // Ensure Unit exists
                // Mapping: unit_code (HRS) -> code (Unit)
                $unit = Unit::firstOrCreate(
                    [
                        'code' => $emp['unit_code'],
                        'company_id' => $company->id
                    ],
                    [
                        'name' => $emp['unit_name'],
                        'division_id' => $division->id
                    ]
                );

                // Ensure Unit links to Division if it was created without it or changed (optional update)
                if ($unit->division_id !== $division->id) {
                    $unit->update(['division_id' => $division->id]);
                }


                // Sync User
                // Mapping: email (HRS) -> email (User)
                // We use 'email' as the primary key for syncing.
                // Could also use 'employee_id' if available in User model (added in migration)

                $userData = [
                    'name' => $emp['full_name'],
                    // 'username' => $emp['nik'], // Assuming NIK can be username, or part of email
                    'employee_id' => $emp['nik'],
                    'unit_id' => $unit->id,
                    'company_id' => $company->id,
                    'department' => $emp['division_name'], // Mapping division name to department string
                    'position' => $emp['unit_name'], // Mapping unit name to position string, just as a fallback
                    // 'phone_number' => $emp['phone_number'], // If user model has phone
                ];

                $user = User::where('email', $emp['email'])->orWhere('employee_id', $emp['nik'])->first();

                if ($user) {
                    // Update
                    $user->update($userData);
                    $counters['users_updated']++;
                } else {
                    // Create
                    $userData['email'] = $emp['email'];
                    $userData['password'] = Hash::make('password'); // Default password
                    $userData['is_first_login'] = true;
                    $userData['username'] = $emp['nik']; // Set username as NIK for uniqueness
                    $userData['role'] = 'user'; // Default role

                    User::create($userData);
                    $counters['users_created']++;
                }
            }

            return redirect()->route('users.index')->with('success', "Sync completed. Created: {$counters['users_created']}, Updated: {$counters['users_updated']}.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred during sync: ' . $e->getMessage());
        }
    }
}

