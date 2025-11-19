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

    public function leaderBoardInfo1($filter, $search)
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

    public function leaderBoardInfo($filter, $search)
    {
        $query = User::where('role', 'PLAYER')
            ->join('profiles', 'users.id', '=', 'profiles.user_id');

        // Search by player name
        if (!empty($search)) {
            $query->where('users.full_name', 'LIKE', '%' . $search . '%');
        }

        if ($filter == 'earnings') {

            $players = $query->orderByDesc('profiles.total_earning')
                ->limit(3)
                ->select('users.id', 'users.full_name', 'profiles.total_earning')
                ->get();

            return [
                'top_player_by_earnings' => $players,
            ];

        } elseif ($filter == 'events') {

            $players = $query->orderByDesc('profiles.total_event_joined')
                ->limit(3)
                ->select('users.id', 'users.full_name', 'profiles.total_event_joined')
                ->get();

            return [
                'top_player_by_events_joined' => $players,
            ];

        } else {

            return [
                'error' => 'Invalid filter. Please use "earnings" or "events".'
            ];
        }
    }


}
