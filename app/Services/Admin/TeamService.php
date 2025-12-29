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
        $teams = Team::with(['player:id,full_name,user_name,role','members.player:id,full_name,user_name,role'])->get();


        foreach ($teams as $team) {
            //
        }

        return $teams;
    }

    public function viewTeam($id)
    {
        $team = Team::with(['player:id,full_name,user_name,role','members.player:id,full_name,user_name,role'])->where('id', $id)->first();

        return $team;
    }
}
