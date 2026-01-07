<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Activitylog\Models\Activity;

use Illuminate\Support\Facades\Gate;

class ActivityLogController extends Controller
{
    public function index()
    {
        if (Gate::denies('admin-only')) {
            abort(403, 'Unauthorized');
        }

        $activities = Activity::with('causer', 'subject')->latest()->paginate(15);

        return view('activity_logs.index', compact('activities'));
    }
}
