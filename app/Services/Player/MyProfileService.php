<?php

namespace App\Services\Player;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MyProfileService
{
    public function __construct()
    {
        //
    }
    public function createTeam($data)
    {
        $max = 2;

        if (Team::where('player_id', Auth::id())->count() == $max) {
            throw ValidationException::withMessages([
                'message' => 'You already have ' . $max . ' teams created. You cannot create more than ' . $max . ' teams.',
            ]);
        }

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
