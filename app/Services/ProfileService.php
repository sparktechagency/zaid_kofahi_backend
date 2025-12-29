<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Follow;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ProfileService
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

        $data['members'][] = strval(Auth::id());

        foreach ($data['members'] as $player_id) {
            TeamMember::create([
                'team_id' => $team->id,
                'player_id' => $player_id,
            ]);
        }

        return $team;

    }
    public function getTeams($user_id)
    {
        return Team::with('members')->where('player_id', $user_id ?? Auth::id())->get();
    }
    public function viewTeam($id)
    {
        $team = Team::with('members.player')->find($id);

        if (!$team) {
            throw ValidationException::withMessages([
                'message' => 'Team id not found.',
            ]);
        }

        return $team;
    }
    public function editTeam($id, $data)
    {
        $team = Team::find($id);

        if (!$team) {
            throw ValidationException::withMessages([
                'message' => 'Team id not found.',
            ]);
        }

        $team->update([
            'name' => $data['name'],
        ]);
        return $team;
    }
    public function deleteTeam($id)
    {
        $team = Team::find($id);

        if (!$team) {
            throw ValidationException::withMessages([
                'message' => 'Team id not found.',
            ]);
        }

        return $team->delete();
    }
    public function organizerProfileInfo()
    {
        $follower_list = Follow::where('follower_id', Auth::id())->whereHas('user', function ($q) {
            $q->where('role', 'PLAYER');
        })->count();

        $total_events = Event::where('organizer_id', Auth::id())->count();
        $completed = Event::where('organizer_id', Auth::id())->where('status', 'Completed')->count();
        $upcoming = Event::where('organizer_id', Auth::id())->where('status', 'Upcoming')->count();
        $canceled = Event::where('organizer_id', Auth::id())->where('status', 'Canceled')->count();

        return [
            'user_info' => User::with('profile')->where('id', Auth::id())->first(),
            'follower_info' => [
                'followings' => 'No following yet.',
                'followers' => $follower_list
            ],
            'events_status' => [
                'total_events' => $total_events,
                'completed' => $completed,
                'upcoming' => $upcoming,
                'canceled' => $canceled
            ],
            'recent_events' => Event::where('organizer_id', Auth::id())
                ->select('id', 'title', 'sport_type', 'status')
                ->latest()
                ->take(5)
                ->get(),
        ];
    }
    public function playerProfileInfo($user_id)
    {

        $userId = $user_id ?? Auth::id();

        $following_list = Follow::where('user_id', $userId)->whereHas('follower', function ($q) {
            $q->where('role', 'PLAYER');
        })->count();

        $follower_list = Follow::where('follower_id', $userId)->whereHas('user', function ($q) {
            $q->where('role', 'PLAYER');
        })->count();

        $profiles = Profile::orderBy('total_earning', 'desc')->get();
        $userProfile = Profile::where('user_id', $userId)->first();
        $rank = $profiles->search(function ($profile) use ($userProfile) {
            return $profile->id === $userProfile->id;
        }) + 1;



        $userEvents = Event::where(function ($query) use ($userId) {
            $query->whereHas('members', function ($q) use ($userId) {
                $q->where('player_id', $userId);
            })
                ->orWhereHas('members.teamMembers', function ($q) use ($userId) {
                    $q->where('player_id', $userId);
                });
        })
            ->select('id', 'title', 'sport_type')
            // ->with(['members', 'members.teamMembers'])
            ->get();

        return [
            'user_info' => User::with('profile')->where('id', $userId)->first(),
            'follower_info' => [
                'is_follow' =>  Follow::where('follower_id', $userId)->where('user_id', Auth::id())->exists(),
                'followings' => $following_list,
                'followers' => $follower_list
            ],
            'events_status' => [
                'events_joined' => $userProfile->total_event_joined,
                'total_winnings' => '$' . $userProfile->total_earning,
                'top_rank' => '#' . $rank
            ],
            'my_events' => $userEvents,
        ];
    }
    public function createReport($data)
    {
        $data['reported_by'] = Auth::id();

        return Report::create($data);
    }
    public function getFollowerFollowingList1($search)
    {
        $follower_list = Follow::with([
            'user' => function ($q) {
                $q->select('id', 'full_name', 'user_name', 'role');
            }
        ])->where('follower_id', Auth::id())->whereHas('user', function ($q) {
            $q->where('role', 'PLAYER');
        })->get();

        $following_list = Follow::with([
            'follower' => function ($q) {
                $q->select('id', 'full_name', 'user_name', 'role');
            }
        ])->where('user_id', Auth::id())->whereHas('follower', function ($q) {
            $q->where('role', 'PLAYER');
        })->get();

        $follower_following_list = $follower_list->merge($following_list);

        return [
            'follower_list' => $follower_list,

            'following_list' => $following_list,

            'follower_following_list' => $follower_following_list
        ];
    }
    public function getFollowerFollowingList($search = null)
    {
        // -------- Followers --------
        $follower_list = Follow::with([
            'user' => function ($q) {
                $q->select('id', 'full_name', 'user_name', 'role');
            }
        ])
            ->where('follower_id', Auth::id())
            ->whereHas('user', function ($q) use ($search) {
                $q->where('role', 'PLAYER');

                if (!empty($search)) {
                    $q->where(function ($query) use ($search) {
                        $query->where('full_name', 'like', "%{$search}%")
                            ->orWhere('user_name', 'like', "%{$search}%");
                    });
                }
            })
            ->get();

        // -------- Following --------
        $following_list = Follow::with([
            'follower' => function ($q) {
                $q->select('id', 'full_name', 'user_name', 'role');
            }
        ])
            ->where('user_id', Auth::id())
            ->whereHas('follower', function ($q) use ($search) {
                $q->where('role', 'PLAYER');

                if (!empty($search)) {
                    $q->where(function ($query) use ($search) {
                        $query->where('full_name', 'like', "%{$search}%")
                            ->orWhere('user_name', 'like', "%{$search}%");
                    });
                }
            })
            ->get();

        // $follower_following_list = $follower_list->merge($following_list);

        $allPlayers = collect()
            ->merge(
                $follower_list->map(fn($f) => $f->user)
            )
            ->merge(
                $following_list->map(fn($f) => $f->follower)
            )
            ->filter()                 // null remove
            ->unique('id')             // unique by id
            ->values();                // reset keys


        return [
            'follower_list' => $follower_list,
            'following_list' => $following_list,
            'players' => $allPlayers
        ];
    }
    public function share($id)
    {
        $event = Event::where('id', $id)->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event not found.',
            ]);
        }

        $event->increment('share');

        return $event;
    }
}
