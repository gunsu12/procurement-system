<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementItem;
use App\Models\ProcurementDocument;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ProcurementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ProcurementRequest::with('user', 'unit', 'company', 'items');

        // Isolation logic based on Company
        $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];

        // Manager can see requests from any company as long as they are the unit approver
        if ($user->role === 'manager') {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('approval_by', $user->id);
            });
        } elseif (!in_array($user->role, $holdingRoles)) {
            // Non-holding roles (except manager) only see their own company
            $query->where('company_id', $user->company_id);

            // Unit role only sees their own unit
            if ($user->role === 'unit') {
                $query->where('unit_id', $user->unit_id);
            }
        }

        // Default Filter Logic for Approval Workflow
        if (!$request->has('status')) {
            $roleDefaults = [
                'manager' => 'submitted',
                'budgeting' => 'approved_by_manager',
                'director_company' => 'approved_by_budgeting',
                'finance_manager_holding' => 'approved_by_dir_company',
                'finance_director_holding' => 'approved_by_fin_mgr_holding',
                'general_director_holding' => 'approved_by_fin_dir_holding',
                'purchasing' => 'processing', // Usually purchasing works on processing items
            ];

            if (isset($roleDefaults[$user->role])) {
                $request->merge(['status' => $roleDefaults[$user->role]]);
            }
        }

        // Auto-filter by is_medical for purchasing users based on their default preference
        // Only apply if user has default_item_purchasing set and is_medical filter is not manually selected
        if ($user->role === 'purchasing' && $user->default_item_purchasing && !$request->has('is_medical')) {
            $isMedical = ($user->default_item_purchasing === 'medis') ? 1 : 0;
            $request->merge(['is_medical' => $isMedical]);
        }

        // Filter by company (for holding roles)
        if ($request->filled('company_id') && in_array($user->role, $holdingRoles)) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by is_medical (medis/non medis)
        if ($request->has('is_medical') && $request->is_medical !== '') {
            $query->where('is_medical', $request->is_medical);
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

        // Pass selection data for filters
        if (in_array($user->role, $holdingRoles)) {
            $units = \App\Models\Unit::with('company')->get();
            $companies = \App\Models\Company::all();
        } else {
            $units = \App\Models\Unit::where('company_id', $user->company_id)->get();
            $companies = collect();
        }

        return view('procurement.index', compact('requests', 'statuses', 'units', 'companies'));
    }

    public function create()
    {
        $user = Auth::user();
        $units = collect();
        $companies = collect();

        if (in_array($user->role, ['super_admin', 'purchasing', 'finance_manager_holding', 'finance_director_holding', 'general_director_holding'])) {
            $units = \App\Models\Unit::all();
            $companies = \App\Models\Company::all();
        }

        return view('procurement.create', compact('units', 'companies'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Idempotence check
        $idempotencyKey = $request->input('idempotency_key');
        if ($idempotencyKey && Cache::has('procurement_idempotency_' . $idempotencyKey)) {
            return redirect()->route('procurement.index')->with('warning', 'Request is already being processed. Please wait.');
        }
        if ($idempotencyKey) {
            Cache::put('procurement_idempotency_' . $idempotencyKey, true, 60); // Expire in 60 seconds
        }

        $isHighLevel = in_array($user->role, ['super_admin', 'purchasing', 'finance_manager_holding', 'finance_director_holding', 'general_director_holding']);

        $rules = [
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
            'document.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ];

        if ($isHighLevel) {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($request, $user, $isHighLevel) {
            $unitId = $isHighLevel ? $request->unit_id : $user->unit_id;

            // Get company_id from the selected unit
            $unit = \App\Models\Unit::findOrFail($unitId);
            $companyId = $unit->company_id;

            $procurement = ProcurementRequest::create([
                'user_id' => $user->id,
                'unit_id' => $unitId,
                'company_id' => $companyId,
                'status' => 'submitted', // Initial status
                'notes' => $request->notes,
                'request_type' => $request->request_type,
                'is_medical' => $request->has('is_medical') ? true : false,
                'is_cito' => $request->has('is_cito') ? true : false,
                'cito_reason' => $request->is_cito ? $request->cito_reason : null,
            ]);

            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $file) {
                    $path = $file->store('documents', 's3');
                    $procurement->documents()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            foreach ($request->items as $item) {
                $procurement->items()->create($item);
            }

            // Log
            $procurement->logs()->create([
                'user_id' => $user->id,
                'action' => 'submitted',
                'note' => 'Initial submission',
                'status_before' => 'draft',
                'status_after' => 'submitted',
            ]);
        });

        return redirect()->route('procurement.index')->with('success', 'Request created successfully.');
    }

    public function edit(ProcurementRequest $procurement)
    {
        $user = Auth::user();

        // Only owner can edit, and only if status is submitted
        if ($procurement->user_id != $user->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($procurement->status != 'submitted') {
            return redirect()->route('procurement.show', $procurement->hashid)->with('error', 'Only submitted requests can be edited.');
        }

        $procurement->load('items');
        return view('procurement.edit', compact('procurement'));
    }

    public function update(Request $request, ProcurementRequest $procurement)
    {
        $this->authorize('update', $procurement);

        // Idempotence check
        $idempotencyKey = $request->input('idempotency_key');
        if ($idempotencyKey && Cache::has('procurement_idempotency_' . $idempotencyKey)) {
            return redirect()->route('procurement.show', $procurement->hashid)->with('warning', 'Update is already being processed. Please wait.');
        }
        if ($idempotencyKey) {
            Cache::put('procurement_idempotency_' . $idempotencyKey, true, 60); // Expire in 60 seconds
        }

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
            'document.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        DB::transaction(function () use ($request, $procurement) {
            $procurement->update([
                'notes' => $request->notes,
                'request_type' => $request->request_type,
                'is_medical' => $request->has('is_medical') ? true : false,
                'is_cito' => $request->has('is_cito') ? true : false,
                'cito_reason' => $request->is_cito ? $request->cito_reason : null,
            ]);

            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $file) {
                    $path = $file->store('documents', 's3');
                    $procurement->documents()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Sync items (delete and recreate for simplicity in this case)
            $procurement->items()->delete();
            foreach ($request->items as $item) {
                $procurement->items()->create($item);
            }

            // Log update
            $procurement->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'note' => 'Request updated by user',
                'status_before' => $procurement->status,
                'status_after' => $procurement->status,
            ]);
        });

        return redirect()->route('procurement.show', $procurement->hashid)->with('success', 'Request updated successfully.');
    }

    public function show(ProcurementRequest $procurement)
    {
        $this->authorize('view', $procurement);

        $procurement->load('items.checkedBy', 'logs.user', 'company', 'unit', 'documents');
        return view('procurement.show', compact('procurement'));
    }

    public function approve(Request $request, ProcurementRequest $procurement)
    {
        $this->authorize('approve', $procurement);

        $user = Auth::user();
        $nextStatus = $this->getNextStatus($procurement->status, $user->role, $procurement->request_type, $procurement->total_amount);

        if (!$nextStatus) {
            return back()->with('error', 'Unauthorized action for this status.');
        }

        // Validasi: Jika status akan berubah ke 'completed', pastikan semua item sudah di-check
        if ($nextStatus === 'completed') {
            $uncheckedItems = $procurement->items()->where('is_checked', false)->count();
            if ($uncheckedItems > 0) {
                return back()->with('error', "Cannot complete request. There are still $uncheckedItems unchecked items.");
            }
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
        $this->authorize('reject', $procurement);

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

    private function getNextStatus($currentStatus, $role, $requestType, $totalAmount)
    {
        $fullChain = [
            'submitted' => ['manager' => 'approved_by_manager'],
            'approved_by_manager' => ['budgeting' => 'approved_by_budgeting'],
            'approved_by_budgeting' => ['director_company' => 'approved_by_dir_company'],
            'approved_by_dir_company' => ['finance_manager_holding' => 'approved_by_fin_mgr_holding'],
            'approved_by_fin_mgr_holding' => ['finance_director_holding' => 'approved_by_fin_dir_holding'],
            'approved_by_fin_dir_holding' => ['general_director_holding' => 'approved_by_gen_dir_holding'],
            'approved_by_gen_dir_holding' => ['purchasing' => 'processing'],
            'processing' => ['purchasing' => 'completed'],
        ];

        $shortChain = [
            'submitted' => ['manager' => 'approved_by_manager'],
            'approved_by_manager' => ['budgeting' => 'approved_by_budgeting'],
            'approved_by_budgeting' => ['purchasing' => 'processing'],
            'processing' => ['purchasing' => 'completed'],
        ];

        // Logic:
        // 1. Asset -> Full Chain
        // 2. Non-Asset >= 1M -> Full Chain
        // 3. Non-Asset < 1M -> Short Chain

        $map = $fullChain; // Default to full

        if ($requestType === 'nonaset' && $totalAmount < 1000000) {
            $map = $shortChain;
        }

        return $map[$currentStatus][$role] ?? null;
    }

    public function toggleItemCheck(Request $request, ProcurementItem $item)
    {
        $this->authorize('toggleItemCheck', $item);

        $user = Auth::user();

        // Toggle the check status
        $item->update([
            'is_checked' => !$item->is_checked,
            'checked_at' => !$item->is_checked ? now() : null,
            'checked_by' => !$item->is_checked ? $user->id : null,
        ]);

        return response()->json([
            'success' => true,
            'is_checked' => $item->is_checked,
            'checked_at' => $item->checked_at ? $item->checked_at->format('d M Y H:i') : null,
            'checked_by' => $item->is_checked ? $user->name : null,
        ]);
    }

    public function deleteDocument(ProcurementDocument $document)
    {
        $procurement = $document->procurementRequest;
        $this->authorize('deleteDocument', $procurement);

        if ($procurement->status != 'submitted') {
            return response()->json(['error' => 'Documents can only be deleted while the request is in submitted status.'], 403);
        }

        try {
            // Delete file from storage
            $deleted = Storage::disk('s3')->delete($document->file_path);

            if (!$deleted) {
                \Log::error("Failed to delete file from S3 at: " . $document->file_path);
                return response()->json(['error' => 'Storage disk refused to delete the file. Check permissions.'], 500);
            }

            // Delete record from DB
            $document->delete();

            return response()->json(['success' => 'Document deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete document: ' . $e->getMessage()], 500);
        }
    }

    public function deleteLegacyDocument(ProcurementRequest $procurement)
    {
        $this->authorize('deleteDocument', $procurement);

        if ($procurement->status != 'submitted') {
            return response()->json(['error' => 'Documents can only be deleted while the request is in submitted status.'], 403);
        }

        try {
            if ($procurement->document_path) {
                $deleted = Storage::disk('s3')->delete($procurement->document_path);

                if (!$deleted) {
                    \Log::error("Failed to delete legacy file from S3 at: " . $procurement->document_path);
                }

                $procurement->update(['document_path' => null]);
            }

            return response()->json(['success' => 'Legacy document deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete document: ' . $e->getMessage()], 500);
        }
    }
    public function rejectItem(Request $request, ProcurementItem $item)
    {
        $this->authorize('rejectItem', $item);

        $procurement = $item->procurementRequest;

        if ($procurement->status !== 'submitted') {
            return response()->json(['error' => 'Items can only be rejected in submitted status.'], 400);
        }

        $item->update([
            'is_rejected' => true,
            'rejection_note' => $request->input('note'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item rejected.',
            'is_rejected' => true,
            'rejection_note' => $item->rejection_note
        ]);
    }

    public function cancelRejectItem(Request $request, ProcurementItem $item)
    {
        $this->authorize('cancelRejectItem', $item);

        $procurement = $item->procurementRequest;

        if ($procurement->status !== 'submitted') {
            return response()->json(['error' => 'Items can only be rejected in submitted status.'], 400);
        }

        $item->update([
            'is_rejected' => false,
            'rejection_note' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item rejection cancelled.',
            'is_rejected' => false
        ]);
    }
}
