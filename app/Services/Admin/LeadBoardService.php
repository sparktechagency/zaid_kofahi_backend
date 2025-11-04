<?php

namespace App\Services\Admin;

use App\Models\User;

class LeadBoardService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function leaderBoardInfo()
    {
        $topEarnings = User::where('role', 'PLAYER')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->orderByDesc('profiles.total_earning')
            ->limit(3)
            ->select('users.id', 'users.full_name', 'profiles.total_earning')
            ->get();

        $topEvents = User::where('role', 'PLAYER')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->orderByDesc('profiles.total_event_joined')
            ->limit(3)
            ->select('users.id', 'users.full_name', 'profiles.total_event_joined')
            ->get();

        return [
            'top_player_by_earnings' => $topEarnings,
            'top_player_by_events_joined' => $topEvents
        ];
    }

}
