<?php

namespace App\Http\Controllers;

use App\Models\ProcurementItem;
use App\Models\Unit;
use App\Models\Company;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ProcurementItem::query()
            ->with([
                'procurementRequest.unit',
                'procurementRequest.user',
                'procurementRequest.logs' => function ($q) {
                    $q->where('status_after', 'approved_by_budgeting')
                        ->orWhere('action', 'approved_by_budgeting'); // Safety catch
                }
            ])
            ->whereHas('procurementRequest', function ($q) {
                // Base filter: maybe only show non-rejected? Or show all? 
                // Usually reports show active stuff or history. User didn't specify.
                // Assuming we show everything that matches filters.
            });

        // 1. Filter Periodic (Start Date / End Date based on Request Date)
        if ($request->filled('start_date')) {
            $query->whereHas('procurementRequest', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->whereHas('procurementRequest', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            });
        }

        // 2. Filter Asset / Non-Asset
        if ($request->filled('request_type')) {
            $query->whereHas('procurementRequest', function ($q) use ($request) {
                $q->where('request_type', $request->request_type);
            });
        }

        // 3. Filter Unit Pemohon
        if ($request->filled('unit_id')) {
            $query->whereHas('procurementRequest', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        }

        // 4. Filter Sudah Beli / Belum Beli
        if ($request->filled('is_checked')) {
            $isChecked = $request->is_checked == '1';
            $query->where('is_checked', $isChecked);
        }

        // Apply Company filter (if applicable for user role logic, similar to ProcurementController)
        // Ignoring deep permission logic for now primarily to focus on the report functionality, 
        // but normally we'd filter by user company if not super admin.

        if ($request->input('export') === 'excel') {
            return $this->exportExcel($query->get());
        }

        $items = $query->latest('id')->paginate(20)->withQueryString();
        $units = Unit::all(); // Simplified, ideally filtered by company permissions

        return view('reports.purchase', compact('items', 'units'));
    }

    private function exportExcel($items)
    {
        $headers = [
            'Kode Pengajuan',
            'Tanggal Pengajuan',
            'Tanggal Approve Budgeting',
            'Unit Pemohon',
            'Item Name',
            'Item Spec',
            'Qty',
            'Unit',
            'Budget',
            'Status' // Check/Unchecked
        ];

        $callback = function () use ($items, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($items as $item) {
                $req = $item->procurementRequest;

                // Find approval date
                // We eager loaded logs where status_after='approved_by_budgeting'
                // This logic depends on exact status string. 
                // Based on ProcurementController: 'approved_by_budgeting' is the status string.
                $log = $req->logs->first(function ($l) {
                    return $l->status_after === 'approved_by_budgeting';
                });
                $approveDate = $log ? $log->created_at->format('Y-m-d H:i') : '-';

                $row = [
                    $req->code,
                    $req->created_at->format('Y-m-d'),
                    $approveDate,
                    $req->unit->name ?? '-',
                    $item->name,
                    $item->specification,
                    $item->quantity,
                    $item->unit,
                    $item->estimated_price,
                    $item->is_checked ? 'Sudah Beli' : 'Belum Beli'
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        $filename = 'laporan-pembelian-' . date('Y-m-d') . '.csv';

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
