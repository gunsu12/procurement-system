<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $query = \App\Models\ProcurementRequest::query();

        // Manager can see stats from any company as long as they are the unit approver
        if ($user->role === 'manager') {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('approval_by', $user->id);
            });
        }

        // Unit role only sees their own unit's stats
        if ($user->role === 'unit') {
            $query->where('unit_id', $user->unit_id)
                ->where('company_id', $user->company_id);
        }

        $stats = $query->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // Define all possible statuses to ensure they appear in dashboard even if 0
        $allStatuses = [
            'submitted' => 'Submitted',
            'approved_by_manager' => 'Mgr Approved',
            'approved_by_budgeting' => 'Budgeting Appr',
            'approved_by_dir_company' => 'Dir Company Appr',
            'approved_by_fin_mgr_holding' => 'Fin Mgr Holding Appr',
            'approved_by_fin_dir_holding' => 'Fin Dir Holding Appr',
            'approved_by_gen_dir_holding' => 'Gen Dir Holding Appr',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'rejected' => 'Rejected'
        ];

        return view('home', compact('stats', 'allStatuses'));
    }
}
