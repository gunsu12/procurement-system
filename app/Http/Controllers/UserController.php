<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}
