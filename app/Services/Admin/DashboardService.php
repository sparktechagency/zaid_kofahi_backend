<?php

namespace App\Services\Admin;

use Carbon\Carbon;

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
            'users' => '48',
            'events' => '15',
            'service' => '15',
            'earning' => '15',
            'recent_activities' => [],
        ];

    }
}
