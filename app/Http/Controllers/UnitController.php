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
        $query = Unit::with('division', 'company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $units = $query->paginate(10)->withQueryString();
        $companies = \App\Models\Company::all();
        return view('units.index', compact('units', 'companies'));
    }

    public function create()
    {
        $divisions = Division::all();
        $companies = \App\Models\Company::all();
        return view('units.create', compact('divisions', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        Unit::create($request->all());

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        $divisions = Division::all();
        $companies = \App\Models\Company::all();
        return view('units.edit', compact('unit', 'divisions', 'companies'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'company_id' => 'required|exists:companies,id',
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
