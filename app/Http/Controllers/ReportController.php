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
            // Manager can see reports from any company as long as they are the unit approver
            if ($user->role === 'manager') {
                $approvedUnitIds = \App\Models\Unit::where('approval_by', $user->id)->pluck('id')->toArray();
                if ($unitId && in_array($unitId, $approvedUnitIds)) {
                    // If specific unit requested and manager is approver, use it
                    $unitId = $unitId;
                } elseif (!$unitId) {
                    // If no specific unit, show all units where manager is approver
                    $query->whereIn('unit_id', $approvedUnitIds);
                    $unitId = null;
                } else {
                    // If unit requested but manager is not approver, show nothing
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Non-holding roles (except manager) restricted to their own company
                $query->where('company_id', $user->company_id);

                // Unit role: only their own unit
                if ($user->role === 'unit') {
                    $unitId = $user->unit_id;
                }
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
            // Manager: see units from any company where they are the approver
            if ($user->role === 'manager') {
                $query->whereHas('unit', function ($q) use ($user) {
                    $q->where('approval_by', $user->id);
                });
            } else {
                // Non-holding roles (except manager) restricted to their own company
                $query->where('company_id', $user->company_id);

                // Unit role: see only their own unit
                if ($user->role === 'unit') {
                    $query->where('unit_id', $user->unit_id);
                }
            }
        }

        $requests = $query->latest()->get();

        return view('reports.outstanding', compact('requests'));
    }

    public function timeline(Request $request)
    {
        $user = \Auth::user();
        $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];

        $query = \App\Models\ProcurementRequest::with([
            'logs' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'logs.user',
            'unit',
            'company'
        ]);

        // Apply access control
        if (!in_array($user->role, $holdingRoles)) {
            // Manager: see units from any company where they are the approver
            if ($user->role === 'manager') {
                $query->whereHas('unit', function ($q) use ($user) {
                    $q->where('approval_by', $user->id);
                });
            } else {
                // Non-holding roles (except manager) restricted to their own company
                $query->where('company_id', $user->company_id);

                // Unit role: see only their own unit
                if ($user->role === 'unit') {
                    $query->where('unit_id', $user->unit_id);
                }
            }
        }

        // Apply filters
        if ($request->filled('company_id') && in_array($user->role, $holdingRoles)) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $requests = $query->latest()->paginate(20);

        // Calculate timeline data for each request
        foreach ($requests as $req) {
            $req->timeline = $this->calculateTimeline($req->logs);
            $req->total_duration = $this->calculateTotalDuration($req->logs);
        }

        // Calculate average durations across all requests
        $averages = $this->calculateAverageDurations($requests);

        $statuses = [
            'submitted',
            'approved_by_manager',
            'approved_by_budgeting',
            'approved_by_dir_company',
            'approved_by_fin_mgr_holding',
            'approved_by_fin_dir_holding',
            'approved_by_gen_dir_holding',
            'processing',
            'completed',
            'rejected'
        ];

        // Selection data for filters
        if (in_array($user->role, $holdingRoles)) {
            $units = \App\Models\Unit::with('company')->get();
            $companies = \App\Models\Company::all();
        } else {
            $units = \App\Models\Unit::where('company_id', $user->company_id)->get();
            $companies = collect();
        }

        return view('reports.timeline', compact('requests', 'averages', 'statuses', 'units', 'companies'));
    }

    private function calculateTimeline($logs)
    {
        $timeline = [];
        $previousLog = null;

        foreach ($logs as $log) {
            $duration = null;

            if ($previousLog) {
                $duration = $previousLog->created_at->diffInMinutes($log->created_at);
            }

            $timeline[] = [
                'status' => $log->status_after,
                'action' => $log->action,
                'user' => $log->user->name ?? 'System',
                'timestamp' => $log->created_at,
                'duration_from_previous' => $duration,
                'note' => $log->note
            ];

            $previousLog = $log;
        }

        return $timeline;
    }

    private function calculateTotalDuration($logs)
    {
        if ($logs->count() < 2) {
            return 0;
        }

        $first = $logs->first();
        $last = $logs->last();

        return $first->created_at->diffInMinutes($last->created_at);
    }

    private function calculateAverageDurations($requests)
    {
        $durations = [];
        $statusTransitions = [];

        foreach ($requests as $req) {
            if ($req->timeline) {
                foreach ($req->timeline as $item) {
                    if ($item['duration_from_previous'] !== null) {
                        $key = $item['status'];

                        if (!isset($statusTransitions[$key])) {
                            $statusTransitions[$key] = [];
                        }

                        $statusTransitions[$key][] = $item['duration_from_previous'];
                    }
                }
            }
        }

        // Calculate averages
        foreach ($statusTransitions as $status => $times) {
            $durations[$status] = [
                'average' => count($times) > 0 ? array_sum($times) / count($times) : 0,
                'min' => count($times) > 0 ? min($times) : 0,
                'max' => count($times) > 0 ? max($times) : 0,
                'count' => count($times)
            ];
        }

        return $durations;
    }
}
