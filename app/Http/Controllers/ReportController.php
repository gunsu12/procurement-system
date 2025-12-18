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
        $unitId = $request->input('unit_id');
        $requests = \App\Models\ProcurementRequest::with('unit', 'user')
            ->when($unitId, function($q) use ($unitId) {
                return $q->where('unit_id', $unitId);
            })
            ->latest()
            ->get();
            
        $units = \App\Models\Unit::all();
        
        return view('reports.unit', compact('requests', 'units', 'unitId'));
    }

    public function outstanding()
    {
        // "Outstanding purchasing > 7 days"
        // Requests that are currently in 'processing' (Purchasing stage) or ready for purchasing
        // and have been in that state or created > 7 days ago?
        // Let's assume "Not Completed" and created > 7 days ago
        
        $dateLimit = now()->subDays(7);
        
        $requests = \App\Models\ProcurementRequest::with('unit', 'items')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'rejected')
            ->where('created_at', '<=', $dateLimit)
            ->get();

        return view('reports.outstanding', compact('requests'));
    }
}
