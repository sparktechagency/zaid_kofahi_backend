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

    public function dashboardInfo()
    {
        return [
            'users' => User::where('role','!=','ADMIN')->latest()->count()??0,
            'events' => Event::latest()->count()?? 0,
            'branch' => Branch::latest()->count() ?? 0,
            'earning' => '$'.Profile::find(Auth::id())->total_earning ?? 0,
            'recent_activities' => Activity::latest()->get() ?? [],
        ];

    }
}
