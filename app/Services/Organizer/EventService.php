<?php

namespace App\Services\Organizer;

use App\Models\Activity;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Profile;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Winner;
use App\Notifications\EventCreateNotification;
use App\Notifications\KickOutNotification;
use App\Notifications\SelectedWinnerAdminNotification;
use App\Notifications\SelectedWinnerNotification;
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

        $data['time'] = Carbon::createFromFormat('g:i A', $data['time'])->format('H:i');  // 15:00

        $event = Event::create($data);

        Activity::create([
            'date' => Carbon::now()->format('Y-m-d'),
            'user' => 'Organizer',
            'action' => 'Create Event',
            'details' => 'Create ‘' . $event->title . '’ event'
        ]);

        $players = User::where('id', '!=', Auth::id())->get();
        $from = Auth::user()->full_name;
        $message = "";

        Auth::user()->notify(new EventCreateNotification('You', $message));

        foreach ($players as $player) {
            $player->notify(new EventCreateNotification($from, $message));
        }

        return $event;
    }
    public function updateEvent1($id, $data)
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
            $event->entry_fee = $data['entry_fee'] ?? $event->entry_fee;
            $event->prize_amount = $data['prize_amount'] ?? $event->prize_amount;
            $event->prize_distribution = $data['prize_distribution'] ?? $event->prize_distribution;
            $event->rules_guidelines = $data['rules_guidelines'] ?? $event->rules_guidelines;
            $event->save();

            return $event;
        }

        return null;
    }
    public function updateEvent($id, $data)
    {
        $event = Event::where('id', $id)->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event id not found.',
            ]);
        }

        if (isset($data['image'])) {
            if ($event->image && Storage::disk('public')->exists(str_replace('/storage/', '', $event->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $event->image));
            }

            $path = $data['image']->store('images', 'public');
            $data['image'] = '/storage/' . $path;
        }

        if (isset($data['time']) && !empty($data['time'])) {
            try {
                // $data['time'] = Carbon::createFromFormat('h:i A', $data['time'])->format('H:i');  // 15:00
                 $data['time'] = Carbon::createFromFormat('g:i A', $data['time'])->format('H:i');  // 15:00
            } catch (Exception $e) {
                return response()->json(['message' => 'Invalid time format'], 400);
            }
        }

        // if (isset($data['prize_distribution'])) {
        //     $data['prize_distribution'] = json_decode($data['prize_distribution'], true);
        // }

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
        $event->entry_fee = $data['entry_fee'] ?? $event->entry_fee;
        $event->prize_amount = $data['prize_amount'] ?? $event->prize_amount;
        $event->prize_distribution = $data['prize_distribution'] ?? $event->prize_distribution;
        $event->rules_guidelines = $data['rules_guidelines'] ?? $event->rules_guidelines;
        $event->image = $data['image'] ?? $event->image;
        $event->save();

        return $event;
    }
    public function getEvents1(?int $per_page, ?string $search, ?string $filter)
    {
        $events = Event::latest()->paginate($per_page ?? 10);
        foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }
        return $events;
    }
    public function getEvents(?int $per_page, ?string $search, ?string $filter)
    {
        $events = Event::query();

        $events->where('organizer_id', Auth::id());

        if (!empty($search)) {
            $events->where('title', 'like', "%$search%");
        }

        if (!empty($filter)) {
            $allowedFilters = [
                'Pending Payment',
                'Upcoming',
                'Cancelled',
                'Ongoing',
                'Event Over',
                'Awaiting Confirmation',
                'Completed',
            ];

            if (in_array($filter, $allowedFilters)) {
                $events->where('status', $filter);
            }
        }

        $events = $events->latest()->paginate($per_page ?? 10);

        foreach ($events as $event) {

            $event->prize_distribution = json_decode($event->prize_distribution);
            // $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');

            if (!empty($event->time)) {
                $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('g:i A');
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

            $event->max = $event->sport_type == 'team' ? $event->number_of_team_required : $event->number_of_player_required;
            $event->joined = ($event->sport_type === 'single') ? $joined_players->count() : $joined_teams->count();

            $event->is_join = EventMember::where('event_id', $event->id)
                ->where(function ($q) {
                    $team = Team::where('player_id', Auth::id())->first();

                    $q->where('player_id', Auth::id());

                    if ($team) {
                        $q->orWhere('team_id', $team->id);
                    }
                })
                ->exists();
        }

        return $events;
    }
    public function viewEvent($id)
    {
        $event = Event::where('id', $id)
            ->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event id not found.',
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


        $event->max = $event->sport_type == 'team' ? $event->number_of_team_required : $event->number_of_player_required;
        $event->joined = ($event->sport_type === 'single') ? $joined_players->count() : $joined_teams->count();

        return $event;
    }
    public function deleteEvent($id)
    {
        $event = Event::where('id', $id)
            ->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event id not found.',
            ]);
        }

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

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event id not found.',
            ]);
        }

        if ($event->status == 'Awaiting Confirmation') {
            throw ValidationException::withMessages([
                'message' => 'You has already selected the winner.',
            ]);
        }

        if ($event->status == 'Event Over') {
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

                $message = 'Prize money : ' . '$' . $item['amount'] . ' | Additional prize : ' . ($item['additional_prize'] == "" ? 'No additional prize yet.' : $item['additional_prize']);
                User::find($item['player_id'])->notify(new SelectedWinnerNotification('', $message, $event->title, $item['place']));
                User::find(1)->notify(new SelectedWinnerAdminNotification(User::find($item['player_id'])->full_name, $message, $event->title, $item['place']));
            }

            $event->status = 'Awaiting Confirmation';
            $event->save();

            return $winners;
        } else {
            throw ValidationException::withMessages([
                'message' => 'Wait until the this event are over.',
            ]);
        }
    }
    public function remove($id, ?int $event_id)
    {
        $event = Event::find($event_id);

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event not found.',
            ]);
        }

        $event_member = EventMember::where('id', $id)->where('event_id', $event_id)->first();
        if (!$event_member) {
            throw ValidationException::withMessages([
                'message' => 'Event member not found.',
            ]);
        }

        $refund_amount = $event->entry_fee;

        if ($event->sport_type == 'team') {
            $team_owner_id = Team::where('id', $event_member->team_id)->first()->player_id;
            Profile::where('user_id', $team_owner_id)->increment('total_balance', $refund_amount);
            $event_member->delete();

            Transaction::create([
                'user_id' => $team_owner_id,
                'type' => 'Refund',
                'message' => '$' . $refund_amount . ' refund in your wallet.',
                'amount' => $refund_amount,
                'data' => Carbon::now()->format('Y-m-d'),
                'status' => 'Completed',
            ]);

            $from = Auth::user()->full_name;
            $message = "";

            User::find($team_owner_id)->notify(new KickOutNotification($from, $message, $event?->title));



            // $event_member = EventMember::find($id);

            // if (!$event_member) {
            //     return $this->sendError('Member not found', [], 404);
            // }

            // $from = Auth::user()->full_name;
            // $message = "";

            // $event = Event::find($request->event_id);

            // if ($event_member->player_id === null) {

            //     $team = Team::find($event_member->team_id);

            //     if ($team && $team->player_id) {
            //         $owner = User::find($team->player_id);

            //         if ($owner) {
            //             $owner->notify(new KickOutNotification($from, $message, $event?->title));
            //         }
            //     }

            // } else {

            //     $player = User::find($event_member->player_id);

            //     if ($player) {
            //         $player->notify(new KickOutNotification($from, $message, $event?->title));
            //     }
            // }


            return true;
        } else {
            Profile::where('user_id', $event_member->player_id)->increment('total_balance', $refund_amount);
            $event_member->delete();

            Transaction::create([
                'user_id' => $event_member->player_id,
                'type' => 'Refund',
                'message' => '$' . $refund_amount . ' refund in your wallet.',
                'amount' => $refund_amount,
                'data' => Carbon::now()->format('Y-m-d'),
                'status' => 'Completed',
            ]);

            $from = Auth::user()->full_name;
            $message = "";

            User::find($event_member->player_id)->notify(new KickOutNotification($from, $message, $event?->title));


            return true;
        }
    }
    public function getEventMembersList($id)
    {
        $event = Event::where('id', $id)->first();

        if (!$event) {
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
    public function eventPay($id)
    {
        $event = Event::where('id', $id)->where('organizer_id', Auth::id())->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event not found.',
            ]);
        }

        if ($event->status != 'Pending Payment') {
            throw ValidationException::withMessages([
                'message' => 'Event pay already paid.',
            ]);
        }

        $prize_amount = $event->prize_amount;

        $profile = Profile::where('user_id', Auth::id())->first();
        $available_balance = $profile->total_balance + $profile->total_earning - ($profile->total_expence + $profile->total_withdraw);

        if (!($prize_amount <= $available_balance)) {
            throw ValidationException::withMessages([
                'message' => 'You don' . "'" . 't have enough money in your wallet.',
            ]);
        }

        $profile->increment('total_expence', $prize_amount);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'type' => 'Event Pay',
            'message' => '$' . $prize_amount . ' event prize deposite.',
            'amount' => $event->prize_amount,
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        $event->status = 'Upcoming';
        $event->save();

        return [
            'evnet' => $event,
            'transaction' => $transaction
        ];
    }
}
