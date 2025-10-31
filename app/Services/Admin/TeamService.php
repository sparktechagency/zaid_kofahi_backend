<?php

namespace App\Services\Admin;

use App\Models\Team;

class TeamService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getTeams()
    {
        return Team::with('members')->get();
    }
}
