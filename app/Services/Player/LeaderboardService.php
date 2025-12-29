<?php

namespace App\Services\Player;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
    public function leaderBoardInfo10($filter, $search, $event_id)
    {
        $event = Event::find($event_id);

        if ($event->sport_type == 'single') {
            $event_members = EventMember::where('event_id', $event_id)->pluck('player_id')->toArray();
        } else {
            $team = EventMember::where('event_id', $event_id)->pluck('team_id');
            $player_ids = TeamMember::whereIn('team_id', $team)->pluck('player_id');
        }

        $query = User::where('role', 'PLAYER')
            ->join('profiles', 'users.id', '=', 'profiles.user_id');

        if ($event->sport_type == 'single') {
            $query->whereIn('user_id', $event_members);
        } else {
            $query->whereIn('user_id', $player_ids);
        }

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
    public function leaderBoardInfo(?string $filter, ?string $search, ?int $event_id = null)
    {
        $query = User::where('role', 'PLAYER')
            ->join('profiles', 'users.id', '=', 'profiles.user_id');

        if ($event_id) {

            $event = Event::find($event_id);

            if (!$event) {
                return [
                    'error' => 'Invalid event_id provided.'
                ];
            }

            if ($event->sport_type === 'single') {

                $playerIds = EventMember::where('event_id', $event_id)
                    ->whereNotNull('player_id')
                    ->pluck('player_id');

                $query->whereIn('users.id', $playerIds);

            } else {

                $teamIds = EventMember::where('event_id', $event_id)
                    ->whereNotNull('team_id')
                    ->pluck('team_id');

                $playerIds = TeamMember::whereIn('team_id', $teamIds)
                    ->pluck('player_id');

                $query->whereIn('users.id', $playerIds);
            }
        }

        if (!empty($search)) {
            $query->where('users.full_name', 'LIKE', "%{$search}%");
        }

        if ($filter === 'earnings') {

            $players = $query
                ->orderByDesc('profiles.total_earning')
                // ->limit(3)
                ->select('users.id', 'users.full_name','users.user_name', 'profiles.total_earning')
                ->get();

            return [
                'top_player_by_earnings' => $players
            ];

        } elseif ($filter === 'events') {

            $players = $query
                ->orderByDesc('profiles.total_event_joined')
                // ->limit(3)
                ->select('users.id', 'users.full_name','users.user_name', 'profiles.total_event_joined')
                ->get();

            return [
                'top_player_by_events_joined' => $players
            ];
        }

        return [
            'error' => 'Invalid filter. Please use "earnings" or "events".'
        ];
    }
    public function getSportNamesOnlyYouJoin1($event_id)
    {
        $event_members = EventMember::latest()->get();

        $arr1 = [];
        $arr2 = [];

        foreach ($event_members as $member) {
            if ($member->player_id != null) {
                $arr1 = EventMember::where('player_id', Auth::id())->pluck('event_id');
            }
        }

        foreach ($event_members as $member) {
            if ($member->team_id != null) {

                $is_have = TeamMember::where('team_id', $member->team_id)->where('player_id', Auth::id())->exists();

                if ($is_have) {
                    $arr2 = EventMember::where('team_id', $member->team_id)->pluck('event_id');
                }
            }
        }



        return $arr1;

    }
    public function getSportNamesOnlyYouJoin()
    {
        $playerId = Auth::id();

        // 1️⃣ Direct player join করা event
        $playerEventIds = EventMember::where('player_id', $playerId)
            ->pluck('event_id');

        // 2️⃣ Team member হিসেবে join করা event
        $teamIds = TeamMember::where('player_id', $playerId)
            ->pluck('team_id');

        $teamEventIds = EventMember::whereIn('team_id', $teamIds)
            ->pluck('event_id');

        // 3️⃣ Merge + unique
        $eventIds = $playerEventIds
            ->merge($teamEventIds)
            ->unique()
            ->values();

        return Event::whereIn('id', $eventIds)->select('id', 'organizer_id', 'sport_type', 'sport_name')->get();
    }

}
