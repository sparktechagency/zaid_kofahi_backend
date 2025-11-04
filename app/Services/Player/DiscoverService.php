<?php

namespace App\Services\Player;

use App\Models\Cash;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Profile;
use App\Models\TeamMember;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DiscoverService
{
    public function __construct()
    {
        //
    }
    public function getEvents(?int $per_page)
    {
        $events = Event::where('status', '!=', 'Pending Payment')->latest()->paginate($per_page ?? 10);

        foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }

        return $events;
    }
    public function singleJoin($id)
    {
        $event = Event::where('id', $id)->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event not found.',
            ]);
        }

        if ($event->sport_type == 'team') {
            throw ValidationException::withMessages([
                'message' => 'This event not for single join.',
            ]);
        }

        if ($event->status == 'Pending Payment') {
            throw ValidationException::withMessages([
                'message' => 'Pending payment in this event.',
            ]);
        }

        $member = EventMember::where('event_id', $id)
            ->where('player_id', Auth::id())
            ->exists();

        if ($member) {
            throw ValidationException::withMessages([
                'message' => 'You are already joined in this event.',
            ]);
        }

        Profile::where('user_id', Auth::id())->increment('total_event_joined', 1);

        $join = EventMember::create([
            'player_id' => Auth::id(),
            'event_id' => $id,
            'joining_date' => Carbon::today(),
        ]);

        return $join;
    }
    public function teamJoin($id, $team_id)
    {
        $event = Event::where('id', $id)->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event not found.',
            ]);
        }

        if ($event->sport_type == 'single') {
            throw ValidationException::withMessages([
                'message' => 'This event not for team join.',
            ]);
        }



        if (!($event->number_of_player_required_in_a_team <= TeamMember::where('team_id', $team_id)->count())) {

            $need_members = $event->number_of_player_required_in_a_team - TeamMember::where('team_id', $team_id)->count();

            throw ValidationException::withMessages([
                'message' => 'Your team does not have enough team members. You need ' . $need_members . ' more members.',
            ]);
        }

        if ($event->status == 'Pending Payment') {
            throw ValidationException::withMessages([
                'message' => 'Pending payment in this event.',
            ]);
        }

        $member = EventMember::where('event_id', $id)
            ->where('team_id', $team_id)
            ->exists();

        if ($member) {
            throw ValidationException::withMessages([
                'message' => 'You are already joined in this event.',
            ]);
        }

        Profile::where('user_id', Auth::id())->increment('total_event_joined', 1);

        $join = EventMember::create([
            'team_id' => $team_id,
            'event_id' => $id,
            'joining_date' => Carbon::today(),
        ]);

        return $join;
    }
    public function viewEvent($id)
    {
        $event = Event::where('id', $id)
            ->first();

        $event->prize_distribution = json_decode($event->prize_distribution);
        $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');

        return $event;
    }
    public function getEventDetails($id)
    {
        $event = Event::where('id', $id)->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event not found.',
            ]);
        }


        $event->prize_distribution = json_decode($event->prize_distribution);
        $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');


        $joined_players = collect();
        $joined_teams = collect();

        if ($event->sport_type === 'single') {
            $joined_players = EventMember::with([
                'player' => function ($q) {
                    $q->select('id', 'full_name', 'user_name', 'avatar');
                }
            ])->where('event_id', $id)->get();
        } else {
            $joined_teams = EventMember::with([
                'team' => function ($q) {
                    $q->select('id', 'name')
                        ->with(['members.player:id,full_name,user_name,role,avatar']);
                }
            ])->where('event_id', $id)->get();

            $joined_teams->each(function ($eventMember) {
                if ($eventMember->team) {
                    $eventMember->team->team_member_count = $eventMember->team->members->count();
                }
            });
        }


        $max = $event->sport_type == 'team' ? $event->number_of_team_required : $event->number_of_player_required;
        $joined = ($event->sport_type === 'single') ? $joined_players->count() : $joined_teams->count();

        return [
            'event' => $event,
            'max' => $max,
            'joined' => $joined,
            $event->sport_type === 'single' ? 'joined_players' : 'joined_teams' => ($event->sport_type === 'single') ? $joined_players : $joined_teams,
            'top_3_winners' => Winner::where('event_id', $event->id)->get(),
            'event_status' => [
                'players_registered' => $joined . '/' . $max,
                'prize_amount' => $event->prize_amount,
                'view' => $event->view
            ],
        ];
    }

    public function createCashRequest($data)
    {
        $cash = Cash::create([
            'event_id' => $data['event_id'],
            'player_id' => $data['team_id'] ? null : $data['player_id'],
            'team_id' => $data['team_id'] ?? null,
            'amount' => $data['amount'],
            'branch_id' => $data['branch_id'],
        ]);

        return $cash;
    }
}
