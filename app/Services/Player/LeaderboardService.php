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

    public function leaderboardInfo()
    {
        $players = User::where('role', 'PLAYER')->latest()->get();

        return $players;
    }
}
