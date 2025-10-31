<?php

namespace App\Services\Organizer;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class PerformanceService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function performanceInfo()
    {
        return [
            'account_reach' => 1.2 . 'k',
            'social_shares' => '63',
            'total_participants' => '142',
            'active_participants' => '87',
            'completed_events' => Event::where('organizer_id', Auth::id())->where('status', 'Completed')->count(),
        ];

    }
}
