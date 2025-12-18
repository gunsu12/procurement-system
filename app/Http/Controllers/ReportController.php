<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('reports.index');
    }

    public function unit(Request $request)
    {
        $user = \Auth::user();
        $unitId = $request->input('unit_id');

        // Isolation logic for Report: restricted roles forced to their own unit
        if (in_array($user->role, ['unit', 'manager'])) {
            $unitId = $user->unit_id;
        }

        $requests = \App\Models\ProcurementRequest::with('unit', 'user')
            ->when($unitId, function($q) use ($unitId) {
                return $q->where('unit_id', $unitId);
            })
            ->latest()
            ->get();
            
        // Admin roles see all units to filter, Unit roles see only theirs in the dropdown
        $units = in_array($user->role, ['unit', 'manager']) 
            ? \App\Models\Unit::where('id', $user->unit_id)->get() 
            : \App\Models\Unit::all();
        
        return view('reports.unit', compact('requests', 'units', 'unitId'));
    }

    public function outstanding()
    {
        $user = \Auth::user();
        $dateLimit = now()->subDays(7);
        
        $query = \App\Models\ProcurementRequest::with('unit', 'items')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'rejected')
            ->where('created_at', '<=', $dateLimit);

        // Isolation logic for Outstanding Report: restricted roles see only their unit
        if (in_array($user->role, ['unit', 'manager'])) {
            $query->where('unit_id', $user->unit_id);
        }

        $requests = $query->latest()->get();

        return view('reports.outstanding', compact('requests'));
    }
}
