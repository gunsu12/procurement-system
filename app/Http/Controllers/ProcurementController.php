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

        if (!in_array($user->role, $holdingRoles)) {
            // Non-holding roles only see their own company
            $query->where('company_id', $user->company_id);

            // Further isolation: Unit and Manager only see their own unit
            if (in_array($user->role, ['unit', 'manager'])) {
                $query->where('unit_id', $user->unit_id);
            }
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
            'document.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        DB::transaction(function () use ($request) {
            $user = Auth::user();
            $procurement = ProcurementRequest::create([
                'user_id' => $user->id,
                'unit_id' => $user->unit_id,
                'company_id' => $user->company_id,
                'status' => 'submitted', // Initial status
                'notes' => $request->notes,
                'request_type' => $request->request_type,
                'is_medical' => $request->has('is_medical') ? true : false,
                'is_cito' => $request->has('is_cito') ? true : false,
                'cito_reason' => $request->is_cito ? $request->cito_reason : null,
            ]);

            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $file) {
                    $path = $file->store('documents', 'public');
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
        $user = Auth::user();

        if ($procurement->user_id != $user->id || $procurement->status != 'submitted') {
            abort(403, 'Unauthorized action.');
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
                    $path = $file->store('documents', 'public');
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
        $user = Auth::user();
        $holdingRoles = ['finance_manager_holding', 'finance_director_holding', 'general_director_holding', 'super_admin'];

        // Authorization check
        if (!in_array($user->role, $holdingRoles)) {
            // Non-holding users must be in the same company
            if ($procurement->company_id != $user->company_id) {
                abort(403, 'Unauthorized access to this procurement request.');
            }

            // Unit and Manager cannot see other units' data within the same company
            if (in_array($user->role, ['unit', 'manager']) && $procurement->unit_id != $user->unit_id) {
                abort(403, 'Unauthorized access to this procurement request.');
            }
        }

        $procurement->load('items.checkedBy', 'logs.user', 'company', 'unit', 'documents');
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

    public function toggleItemCheck(Request $request, ProcurementItem $item)
    {
        $user = Auth::user();

        // Only purchasing team can check items
        if ($user->role !== 'purchasing') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only items in purchasing phase can be checked
        $procurement = $item->procurementRequest;
        if ($procurement->status !== 'processing') {
            return response()->json(['error' => 'Items can only be checked in purchasing phase'], 400);
        }

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
        $user = Auth::user();
        $procurement = $document->procurementRequest;

        // Authorization: Only owner can delete if status is submitted
        if ($procurement->user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        if ($procurement->status != 'submitted') {
            return response()->json(['error' => 'Documents can only be deleted while the request is in submitted status.'], 403);
        }

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
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
        $user = Auth::user();

        // Authorization
        if ($procurement->user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        if ($procurement->status != 'submitted') {
            return response()->json(['error' => 'Documents can only be deleted while the request is in submitted status.'], 403);
        }

        try {
            if ($procurement->document_path) {
                if (Storage::disk('public')->exists($procurement->document_path)) {
                    Storage::disk('public')->delete($procurement->document_path);
                }
                $procurement->update(['document_path' => null]);
            }

            return response()->json(['success' => 'Legacy document deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete document: ' . $e->getMessage()], 500);
        }
    }
}
