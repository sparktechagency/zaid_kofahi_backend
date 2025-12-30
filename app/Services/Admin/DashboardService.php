<?php

namespace App\Services\Admin;

use App\Models\Activity;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function dashboardInfo($per_page)
    {
        return [
            'users' => User::where('role', '!=', 'ADMIN')->latest()->count(),
            'events' => Event::latest()->count(),
            'branch' => Branch::latest()->count(),
            'earning' => '$' . Profile::find(Auth::id())->total_earning,
            'recent_activities' => Activity::latest()->paginate($per_page ?? 10)
        ];

    }
}
