<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Division;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Unit::with('division', 'company', 'approver');

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by division
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        // Filter by approver
        if ($request->filled('approval_by')) {
            $query->where('approval_by', $request->approval_by);
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('code', 'ILIKE', "%{$search}%");
            });
        }

        $units = $query->paginate(10)->appends($request->except('page'));
        $companies = \App\Models\Company::all();
        $users = \App\Models\User::all();

        // Get divisions for the selected company
        $divisions = collect();
        if ($request->filled('company_id')) {
            $divisions = Division::where('company_id', $request->company_id)->get();
        }

        return view('units.index', compact('units', 'companies', 'divisions', 'users'));
    }

    public function create()
    {
        $divisions = Division::all();
        $companies = \App\Models\Company::all();
        $users = \App\Models\User::all();
        return view('units.create', compact('divisions', 'companies', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:units',
            'division_id' => 'required|exists:divisions,id',
            'company_id' => 'required|exists:companies,id',
            'approval_by' => 'nullable|exists:users,id',
        ]);

        Unit::create($request->all());

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        $divisions = Division::all();
        $companies = \App\Models\Company::all();
        $users = \App\Models\User::all();
        return view('units.edit', compact('unit', 'divisions', 'companies', 'users'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:units,code,' . $unit->id,
            'division_id' => 'required|exists:divisions,id',
            'company_id' => 'required|exists:companies,id',
            'approval_by' => 'nullable|exists:users,id',
        ]);

        $unit->update($request->all());

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }

    public function getUnits(Request $request)
    {
        $query = Unit::with('company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $units = $query->get();

        return response()->json($units);
    }
}
