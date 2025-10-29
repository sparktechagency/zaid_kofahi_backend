<?php

namespace App\Services\Player;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Auth;

class MyProfileService
{
    public function __construct()
    {
        //
    }
    public function createTeam($data)
    {
        $team = Team::create([
            'player_id' => Auth::id(),
            'name' => $data['name'],
        ]);

        foreach ($data['members'] as $player_id) {
            TeamMember::create([
                'team_id' => $team->id,
                'player_id' => $player_id,
            ]);
        }

        return $team;

    }
    public function getTeams()
    {
        return Team::with('members')->where('player_id', Auth::id())->get();
    }
    public function viewTeam($id)
    {
        return Team::findOrFail($id);
    }
    public function editTeam($id, $data)
    {
        $team = Team::findOrFail($id);
        $team->update([
            'name' => $data['name'],
        ]);
        return $team;
    }
    public function deleteTeam($id)
    {
        $team = Team::findOrFail($id);
        return $team->delete();
    }
}
