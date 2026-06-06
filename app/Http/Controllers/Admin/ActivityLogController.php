<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = Activity::latest()->paginate(20);

        return view('admin.activities.index', compact('activities'));
    }
}