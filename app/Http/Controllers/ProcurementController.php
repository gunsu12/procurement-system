<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementItem;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcurementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ProcurementRequest::with('user', 'unit');

        // Isolation logic: Unit and Manager only see their own unit
        if (in_array($user->role, ['unit', 'manager'])) {
            $query->where('unit_id', $user->unit_id);
        }

        // Filter by unit (for roles that can see multiple units, e.g., budgeting, purchasing, directors)
        if ($request->filled('unit_id') && !in_array($user->role, ['unit', 'manager'])) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by duration
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $requests = $query->latest()->paginate(10);
        
        $statuses = [
            'submitted', 'approved_by_manager', 'approved_by_budgeting',
            'approved_by_dir_company', 'approved_by_fin_mgr_holding',
            'approved_by_fin_dir_holding', 'approved_by_gen_dir_holding',
            'processing', 'completed', 'rejected'
        ];

        // Pass units for filter if user is not restricted
        $units = !in_array($user->role, ['unit', 'manager']) ? \App\Models\Unit::all() : collect();

        return view('procurement.index', compact('requests', 'statuses', 'units'));
    }

    public function create()
    {
        return view('procurement.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.estimated_price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'request_type' => 'required|in:aset,nonaset',
            'is_medical' => 'nullable|boolean',
            'is_cito' => 'nullable|boolean',
            'cito_reason' => 'required_if:is_cito,1|nullable|string|max:1000',
            'document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        DB::transaction(function () use ($request) {
            $path = null;
            if ($request->hasFile('document')) {
                $path = $request->file('document')->store('documents', 'public');
            }

            $procurement = ProcurementRequest::create([
                'user_id' => Auth::id(),
                'unit_id' => Auth::user()->unit_id,
                'status' => 'submitted', // Initial status
                'notes' => $request->notes,
                'request_type' => $request->request_type,
                'is_medical' => $request->has('is_medical') ? true : false,
                'is_cito' => $request->has('is_cito') ? true : false,
                'cito_reason' => $request->is_cito ? $request->cito_reason : null,
                'document_path' => $path,
            ]);

            foreach ($request->items as $item) {
                $procurement->items()->create($item);
            }

            // Log
            $procurement->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'submitted',
                'note' => 'Initial submission',
                'status_before' => 'draft',
                'status_after' => 'submitted',
            ]);
        });

        return redirect()->route('procurement.index')->with('success', 'Request created successfully.');
    }

    public function show(ProcurementRequest $procurement)
    {
        $user = Auth::user();
        
        // Authorization check: Unit and Manager cannot see other units' data
        if (in_array($user->role, ['unit', 'manager']) && $procurement->unit_id != $user->unit_id) {
            abort(403, 'Unauthorized access to this procurement request.');
        }

        $procurement->load('items', 'logs.user');
        return view('procurement.show', compact('procurement'));
    }

    public function approve(Request $request, ProcurementRequest $procurement)
    {
        // Validation role vs status logic here
        $user = Auth::user();
        $nextStatus = $this->getNextStatus($procurement->status, $user->role);
        
        if (!$nextStatus) {
            return back()->with('error', 'Unauthorized action for this status.');
        }

        DB::transaction(function () use ($procurement, $request, $user, $nextStatus) {
            $oldStatus = $procurement->status;
            $procurement->update(['status' => $nextStatus]);

            $procurement->logs()->create([
                'user_id' => $user->id,
                'action' => 'approved',
                'note' => $request->input('note'),
                'status_before' => $oldStatus,
                'status_after' => $nextStatus,
            ]);
        });

        return back()->with('success', 'Request approved.');
    }

    public function reject(Request $request, ProcurementRequest $procurement)
    {
        DB::transaction(function () use ($procurement, $request) {
            $oldStatus = $procurement->status;
            $procurement->update(['status' => 'rejected']);

            $procurement->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'rejected',
                'note' => $request->input('note', 'Rejected'),
                'status_before' => $oldStatus,
                'status_after' => 'rejected',
            ]);
        });

        return back()->with('success', 'Request rejected.');
    }

    private function getNextStatus($currentStatus, $role)
    {
        // 1. unit -> submitted
        // 2. manager -> approved_by_manager
        // 3. budgeting -> approved_by_budgeting
        // 4. director_company -> approved_by_dir_company
        // 5. finance_mgr_holding -> approved_by_fin_mgr_holding
        // 6. finance_dir_holding -> approved_by_fin_dir_holding
        // 7. general_dir_holding -> approved_by_gen_dir_holding
        // 8. purchasing -> processing -> completed

        $map = [
            'submitted' => ['manager' => 'approved_by_manager'],
            'approved_by_manager' => ['budgeting' => 'approved_by_budgeting'],
            'approved_by_budgeting' => ['director_company' => 'approved_by_dir_company'],
            'approved_by_dir_company' => ['finance_manager_holding' => 'approved_by_fin_mgr_holding'],
            'approved_by_fin_mgr_holding' => ['finance_director_holding' => 'approved_by_fin_dir_holding'],
            'approved_by_fin_dir_holding' => ['general_director_holding' => 'approved_by_gen_dir_holding'],
            'approved_by_gen_dir_holding' => ['purchasing' => 'processing'],
            'processing' => ['purchasing' => 'completed'],
        ];

        return $map[$currentStatus][$role] ?? null;
    }
}
