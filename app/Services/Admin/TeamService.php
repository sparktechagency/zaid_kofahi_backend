<?php

namespace App\Services\Admin;

use App\Models\Team;
use Carbon\Carbon;

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

    public function viewTeam($id)
    {
        $event = Team::with('members')
            ->where('id', $id)
            ->first();

        return $event;
    }
}
