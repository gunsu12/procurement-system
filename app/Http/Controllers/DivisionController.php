<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Division::with('company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $divisions = $query->paginate(10)->withQueryString();
        $companies = \App\Models\Company::all();
        return view('divisions.index', compact('divisions', 'companies'));
    }

    public function create()
    {
        $companies = \App\Models\Company::all();
        return view('divisions.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
        ]);

        Division::create($request->all());

        return redirect()->route('divisions.index')->with('success', 'Division created successfully.');
    }

    public function edit(Division $division)
    {
        $companies = \App\Models\Company::all();
        return view('divisions.edit', compact('division', 'companies'));
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
        ]);

        $division->update($request->all());

        return redirect()->route('divisions.index')->with('success', 'Division updated successfully.');
    }

    public function destroy(Division $division)
    {
        $division->delete();

        return redirect()->route('divisions.index')->with('success', 'Division deleted successfully.');
    }

    public function getDivisions(Request $request)
    {
        $query = Division::query();

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $divisions = $query->get(['id', 'name', 'code', 'company_id']);

        return response()->json($divisions);
    }
}
