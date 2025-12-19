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
        $companyId = $request->input('company_id');

        $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];

        $query = \App\Models\ProcurementRequest::with('unit', 'user', 'company');

        if (!in_array($user->role, $holdingRoles)) {
            // Non-holding roles restricted to their own company
            $query->where('company_id', $user->company_id);

            // Further isolation for unit/manager
            if (in_array($user->role, ['unit', 'manager'])) {
                $unitId = $user->unit_id;
            }
        }

        $requests = $query->when($companyId, function ($q) use ($companyId) {
            return $q->where('company_id', $companyId);
        })
            ->when($unitId, function ($q) use ($unitId) {
                return $q->where('unit_id', $unitId);
            })
            ->latest()
            ->get();

        // Selection data
        if (in_array($user->role, $holdingRoles)) {
            $units = \App\Models\Unit::all();
            $companies = \App\Models\Company::all();
        } else {
            $units = \App\Models\Unit::where('company_id', $user->company_id)->get();
            $companies = collect();
        }

        return view('reports.unit', compact('requests', 'units', 'unitId', 'companies', 'companyId'));
    }

    public function outstanding()
    {
        $user = \Auth::user();
        $dateLimit = now()->subDays(7);
        $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];

        $query = \App\Models\ProcurementRequest::with('unit', 'items', 'company')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'rejected')
            ->where('created_at', '<=', $dateLimit);

        if (!in_array($user->role, $holdingRoles)) {
            // Non-holding roles restricted to their own company
            $query->where('company_id', $user->company_id);

            // Isolation logic for Outstanding Report: restricted roles see only their unit
            if (in_array($user->role, ['unit', 'manager'])) {
                $query->where('unit_id', $user->unit_id);
            }
        }

        $requests = $query->latest()->get();

        return view('reports.outstanding', compact('requests'));
    }
}
