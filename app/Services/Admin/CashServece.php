<?php

namespace App\Services\Admin;

use App\Models\Cash;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Profile;
use App\Models\TeamMember;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CashServece
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function getCashRequests()
    {
        $cashes = Cash::with([
            'player' => function ($q) {
                $q->select('id', 'full_name');
            },
            'team.player' => function ($q) {
                $q->select('id', 'full_name');
            },
            'event' => function ($q) {
                $q->select('id', 'title', 'sport_type');
            },
            'branch'
        ])->latest()->get();

        return $cashes;
    }
    public function cashVerification($id)
    {
        $cash = Cash::where('id', $id)->first();

        if (!$cash) {
            throw ValidationException::withMessages([
                'message' => 'Cash request ID not found.',
            ]);
        }

        $cash->status = 'Verified';
        $cash->save();

        return $cash;
    }
    public function deleteRequest($id)
    {
        $cash = Cash::find($id);

        if (!$cash) {
            throw ValidationException::withMessages([
                'message' => 'Cash request ID not found.',
            ]);
        }

        $cash->delete();

        return true;
    }
    public function cashSingleJoin($player_id, $id)
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

        $joined_players = collect();
        $joined_teams = collect();

        if ($event->sport_type === 'single') {
            $joined_players = EventMember::with([
                'player' => function ($q) {
                    $q->select('id', 'full_name', 'user_name', 'avatar');
                }
            ])->where('event_id', $event->id)->get();
        } else {
            $joined_teams = EventMember::with([
                'team' => function ($q) {
                    $q->select('id', 'name')
                        ->with(['members.player:id,full_name,user_name,role,avatar']);
                }
            ])->where('event_id', $event->id)->get();

            $joined_teams->each(function ($eventMember) {
                if ($eventMember->team) {
                    $eventMember->team->team_member_count = $eventMember->team->members->count();
                }
            });
        }

        $max = $event->sport_type == 'team' ? $event->number_of_team_required : $event->number_of_player_required;
        $joined = ($event->sport_type === 'single') ? $joined_players->count() : $joined_teams->count();

        if ($max == $joined) {
            throw ValidationException::withMessages([
                'message' => 'You can' . "'" . 't join because the event is already full.',
            ]);
        }

        $join = EventMember::create([
            'player_id' => $player_id ?? Auth::id(),
            'event_id' => $id,
            'joining_date' => Carbon::today(),
        ]);

        Profile::where('user_id', Auth::id())->increment('total_event_joined', 1);

        

        $transaction = Transaction::create([
            'user_id' => $player_id,
            'event_id' => $event->id,
            'type' => 'Entry Fee',
            'message' => '$' . $event->entry_fee . ' entry fee given.',
            'amount' => $event->entry_fee,
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        return [
            'join' => $join,
            'transaction' => $transaction
        ];
    }
    public function cashTeamJoin($id, $team_id)
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

        $join = EventMember::create([
            'team_id' => $team_id,
            'event_id' => $id,
            'joining_date' => Carbon::today(),
        ]);

        Profile::where('user_id', Auth::id())->increment('total_event_joined', 1);

        $transaction = Transaction::create([
            'user_id' => $team_id,
            'event_id' => $event->id,
            'type' => 'Entry Fee',
            'message' => '$' . $event->entry_fee . ' entry fee given.',
            'amount' => $event->entry_fee,
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        return [
            'join' => $join,
            'transaction' => $transaction
        ];
    }
}
