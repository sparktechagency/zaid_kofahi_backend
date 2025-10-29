<?php

namespace App\Services\Player;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\TeamMember;
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
                'message' => 'Your team does not have enough team members. You need '.$need_members.' more members.',
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

        $join = EventMember::create([
            'team_id' => $team_id,
            'event_id' => $id,
            'joining_date' => Carbon::today(),
        ]);

        return $join;
    }
}
