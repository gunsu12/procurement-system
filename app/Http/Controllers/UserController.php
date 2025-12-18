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

    public function index()
    {
        $users = User::with('unit')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $units = Unit::all();
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
        return view('users.create', compact('units', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'unit_id' => $request->unit_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $units = Unit::all();
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
        return view('users.edit', compact('user', 'units', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'unit_id' => $request->unit_id,
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
