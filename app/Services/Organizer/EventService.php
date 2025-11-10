<?php

namespace App\Services\Organizer;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Profile;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\Winner;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventService
{
    public function __construct()
    {
        //
    }
    public function createEvent($data)
    {
        $data['organizer_id'] = Auth::id();

        if (isset($data['image'])) {
            $path = $data['image']->store('images', 'public');
            $data['image'] = '/storage/' . $path;
        }

        $data['time'] = Carbon::createFromFormat('h:i A', $data['time'])->format('H:i');  // 15:00

        return Event::create($data);
    }
    public function updateEvent($id, $data)
    {
        $event = Event::where('id', $id)
            ->first();

        if ($event) {
            $event = Event::where('id', $id)
                ->first();

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            if (isset($data['image'])) {
                if ($event->image && Storage::disk('public')->exists(str_replace('/storage/', '', $event->image))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $event->image));
                }

                $path = $data['image']->store('images', 'public');
                $data['image'] = '/storage/' . $path;
            }

            $data['time'] = Carbon::createFromFormat('h:i A', $data['time'])->format('H:i');  // 15:00

            $event->title = $data['title'] ?? $event->title;
            $event->description = $data['description'] ?? $event->description;
            $event->sport_type = $data['sport_type'] ?? $event->sport_type;
            $event->starting_date = $data['starting_date'] ?? $event->starting_date;
            $event->ending_date = $data['ending_date'] ?? $event->ending_date;
            $event->time = $data['time'] ?? $event->time;
            $event->location = $data['location'] ?? $event->location;
            $event->number_of_player_required = $data['number_of_player_required'] ?? $event->number_of_player_required;
            $event->number_of_team_required = $data['number_of_team_required'] ?? $event->number_of_team_required;
            $event->number_of_player_required_in_a_team = $data['number_of_player_required_in_a_team'] ?? $event->number_of_player_required_in_a_team;
            $event->entry_free = $data['entry_free'] ?? $event->entry_free;
            $event->prize_amount = $data['prize_amount'] ?? $event->prize_amount;
            $event->prize_distribution = $data['prize_distribution'] ?? $event->prize_distribution;
            $event->rules_guidelines = $data['rules_guidelines'] ?? $event->rules_guidelines;
            $event->save();

            return $event;
        }

        return null;
    }
    public function getEvents(?int $per_page)
    {
        $events = Event::latest()->paginate($per_page ?? 10);
        foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }
        return $events;
    }
    public function viewEvent($id)
    {
        $event = Event::where('id', $id)
            ->first();

        $event->prize_distribution = json_decode($event->prize_distribution);
        $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');

        return $event;
    }
    public function deleteEvent($id)
    {
        $event = Event::where('id', $id)
            ->first();

        if ($event && $event->status == 'Pending Payment') {
            $event->delete();
            return true;
        }

        return false;
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
    public function selectedWinner($data, $id)
    {

        $event = Event::where('id', $id)->first();

        if($event->status == 'Awaiting Confirmation'){
            throw ValidationException::withMessages([
                'message' => 'You has already selected the winner.',
            ]);
        }

        // if ($event->status == 'Event Over') {
            $data = is_string($data) ? json_decode($data, true) : $data;
            $winners = [];
            foreach ($data as $item) {
                $winners[] = Winner::create([
                    'event_id' => $id,
                    'place' => $item['place'],
                    'player_id' => $item['player_id'],
                    'team_id' => $item['team_id'] ?? null,
                    'amount' => $item['amount'],
                    'additional_prize' => $item['additional_prize'] ?? null,
                ]);
            }

            $event->status = 'Awaiting Confirmation';
            $event->save();

            return $winners;
        // } else {
        //     throw ValidationException::withMessages([
        //         'message' => 'Wait until the this event are over.',
        //     ]);
        // }
    }
    public function remove($id)
    {
        $event_member = EventMember::where('id', $id)->first();

        if (!$event_member) {
            throw ValidationException::withMessages([
                'message' => 'Event member not found.',
            ]);
        }

        if ($event_member->player_id == null) {
            $refund_amount = Event::where('id', $event_member->event_id)->first()->entry_free;

            $team_owner_id = Team::where('id', $event_member->team_id)->first()->player_id;

            Profile::where('user_id', $team_owner_id)->increment('total_balance', $refund_amount);

            $event_member->delete();
            return true;
        } else {
            $refund_amount = Event::where('id', $event_member->event_id)->first()->entry_free;
            Profile::where('user_id', $event_member->player_id)->increment('total_balance', $refund_amount);
            $event_member->delete();
            return true;
        }
    }
    public function getEventMembersList($id)
    {
        $event = Event::where('id', $id)->first();

         if(!$event){
             throw ValidationException::withMessages([
                'message' => 'Event ID not found.',
            ]);
        }

        if ($event->sport_type == 'single') {
            $event_members = EventMember::with([
                'player' => function ($q) {
                    $q->select('id', 'full_name', 'user_name', 'role', 'avatar');
                }
            ])
                ->where('event_id', $id)
                ->get();
        } else {
            $event_members = EventMember::with([
                'team' => function ($q) {
                    $q->select('id', 'name')
                        ->with(['members.player:id,full_name,user_name,role,avatar']);
                }
            ])->where('event_id', $id)->get();
        }

        return $event_members;

    }
}
