<?php

namespace App\Services\Player;

use App\Models\User;

class LeaderboardService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function leaderBoardInfo($filter)
    {
        if ($filter == 'earnings') {

            $topPlayers = User::where('role', 'PLAYER')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->orderByDesc('profiles.total_earning')
                ->limit(3)
                ->select('users.id', 'users.full_name', 'profiles.total_earning')
                ->get();

            return [
                'top_player_by_earnings' => $topPlayers,
            ];
        } elseif ($filter == 'events') {
            $topPlayers = User::where('role', 'PLAYER')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->orderByDesc('profiles.total_event_joined')
                ->limit(3)
                ->select('users.id', 'users.full_name', 'profiles.total_event_joined')
                ->get();

            return [
                'top_player_by_events_joined' => $topPlayers,
            ];
        } else {
            return [
                'error' => 'Invalid filter. Please use "earnings" or "events".'
            ];
        }
    }

}
