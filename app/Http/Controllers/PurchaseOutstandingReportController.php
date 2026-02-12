<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\Unit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PurchaseOutstandingReportController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();
        $results = null;

        if ($request->has('preview') || $request->has('export')) {
            $query = ProcurementRequest::query()
                ->where('status', 'processing')
                ->with([
                    'items',
                    'unit',
                    'logs' => function ($q) {
                        $q->where('status_after', 'processing')->latest();
                    }
                ]);

            // Filter Date (Tanggal mulai proses)
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();

                $query->whereHas('logs', function ($q) use ($start, $end) {
                    $q->where('status_after', 'processing')
                        ->whereBetween('created_at', [$start, $end]);
                });
            }

            // Filter Type (Asset / Non-Asset)
            if ($request->filled('request_type')) {
                $query->where('request_type', $request->request_type);
            }

            // Filter Is Medical
            if ($request->filled('is_medical')) {
                $isMedical = $request->is_medical === 'yes' ? 1 : 0;
                $query->where('is_medical', $isMedical);
            }

            // Filter Unit
            if ($request->filled('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }

            // Get Data
            $requests = $query->get();

            // Filter Outstanding > 7 Days & Calculate Logic
            $filteredData = [];
            foreach ($requests as $req) {
                // Determine Processed Date
                $log = $req->logs->first(); // Since we filtered query logs by 'processing'

                // Fallback if no specific log found (migrated data?), use updated_at
                $processedAt = $log ? $log->created_at : $req->updated_at;

                $outstandingDays = round(now()->diffInDays($processedAt));

                if ($request->filled('outstanding_filter') && $request->outstanding_filter === 'more_than_7') {
                    if ($outstandingDays <= 7) {
                        continue;
                    }
                }

                foreach ($req->items as $item) {
                    // Only include items not checked? Or all items in the request? 
                    // Prompt implies "Purchase Outstanding (...) belum di beli".
                    // If we have a checklist, maybe we should filter items that are NOT checked?
                    // User prompt: "permintaan yang sudah di proses purchasing, tapi belum di beli"
                    // If the request is 'processing', it means it is not 'completed'.
                    // However, if individual items are 'checked' (bought), should they be excluded?
                    // Let's assume YES, exclude checked items if checklist is used.
                    // But to be safe, I will include ALL items for now or check 'is_checked'.
                    // Let's include all items of the processing request but add status column potentially, 
                    // OR strictly follow "Outstanding" = "Not done".
                    // I will filter out checked items if is_checked is true.

                    if ($item->is_checked)
                        continue;

                    $filteredData[] = [
                        'code' => $req->code,
                        'created_at' => $req->created_at->format('Y-m-d'),
                        'unit' => $req->unit->name,
                        'category' => $req->is_medical ? 'Medis' : 'Non Medis',
                        'item_name' => $item->name,
                        'item_spec' => $item->specification,
                        'processed_at' => $processedAt->format('Y-m-d'),
                        'outstanding_days' => $outstandingDays,
                    ];
                }
            }

            $results = collect($filteredData);

            if ($request->has('export')) {
                return $this->exportToExcel($results);
            }
        }

        return view('reports.purchase_outstanding.index', compact('units', 'results'));
    }

    private function exportToExcel($data)
    {
        $filename = "Purchase_Outstanding_" . date('YmdHis') . ".xls";

        return response()->streamDownload(function () use ($data) {
            echo "<html>";
            echo "<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head>";
            echo "<body>";
            echo "<table border='1'>";
            echo "<thead>
                    <tr>
                        <th>Kode Request</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Unit</th>
                        <th>Medis/Non Medis</th>
                        <th>Nama Barang</th>
                        <th>Spesifikasi Barang</th>
                        <th>Tanggal Di Proses</th>
                        <th>Usia Outstanding (Hari)</th>
                    </tr>
                  </thead>";
            echo "<tbody>";

            // Helper to sanitize Excel injection
            $sanitize = function ($value) {
                if (is_string($value) && preg_match('/^[-+=@]/', $value)) {
                    return "'" . $value;
                }
                return $value;
            };

            foreach ($data as $row) {
                echo "<tr>";
                echo "<td>" . $sanitize($row['code']) . "</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "<td>" . $sanitize($row['unit']) . "</td>";
                echo "<td>{$row['category']}</td>";
                echo "<td>" . $sanitize($row['item_name']) . "</td>";
                echo "<td>" . $sanitize($row['item_spec']) . "</td>";
                echo "<td>{$row['processed_at']}</td>";
                echo "<td>{$row['outstanding_days']}</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</body>";
            echo "</html>";
        }, $filename, [
            "Content-Type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=\"$filename\""
        ]);
    }
}
