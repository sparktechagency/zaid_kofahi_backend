<?php

namespace App\Services\Player;

use App\Models\Activity;
use App\Models\Branch;
use App\Models\Cash;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Follow;
use App\Models\Profile;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Winner;
use App\Notifications\EventJoinNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DiscoverService
{
    public function __construct()
    {
        //
    }

    public function getEvents(?int $per_page, ?string $search, ?string $filter)
    {
        $events = Event::query()
            ->where('status', '!=', 'Pending Payment');

        if (!empty($search)) {
            $events->where('title', 'LIKE', '%' . $search . '%');
        }

        if (!empty($filter)) {

            if ($filter === 'today') {
                $events->whereDate('created_at', Carbon::today());
            } elseif ($filter === 'tomorrow') {
                $events->whereDate('starting_date', Carbon::tomorrow());
            } elseif ($filter === 'upcoming') {
                $events->where('status', 'Upcoming');
            } elseif ($filter === 'weekend') {
                // $events->whereIn(DB::raw('DAYOFWEEK(created_at)'), [6, 7]);

                $events->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(), // Monday
                    Carbon::now()->endOfWeek()    // Sunday
                ]);
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
    public function singleJoin($player_id, $id)
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

        $entry_fee = $event->entry_fee;
        $profile = Profile::where('user_id', Auth::id())->first();
        $available_balance = $profile->total_balance + $profile->total_earning - ($profile->total_expence + $profile->total_withdraw);

        if (!($entry_fee <= $available_balance)) {
            throw ValidationException::withMessages([
                'message' => 'You don' . "'" . 't have enough money in your wallet.',
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

        $profile->increment('total_expence', $entry_fee);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'type' => 'Entry Fee',
            'message' => '$' . $entry_fee . ' entry fee given.',
            'amount' => $event->entry_fee,
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        Activity::create([
            'date' => Carbon::now()->format('Y-m-d'),
            'user' => 'Player',
            'action' => 'Join Event',
            'details' => 'Join ‘' . $event->title . '’ by paying ' . $entry_fee
        ]);

        $users = User::where('id', '!=', Auth::id())->get();
        $from = Auth::user()->full_name;
        $message = "";

        Auth::user()->notify(new EventJoinNotification('You', $message, $event->title));

        foreach ($users as $user) {
            $user->notify(new EventJoinNotification($from, $message, $event->title));
        }

        return [
            'join' => $join,
            'transaction' => $transaction
        ];
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

        $entry_fee = $event->entry_fee;

        $profile = Profile::where('user_id', Auth::id())->first();
        $available_balance = $profile->total_balance + $profile->total_earning - ($profile->total_expence + $profile->total_withdraw);

        if (!($entry_fee <= $available_balance)) {
            throw ValidationException::withMessages([
                'message' => 'You don' . "'" . 't have enough money in your wallet.',
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
            'team_id' => $team_id,
            'event_id' => $id,
            'joining_date' => Carbon::today(),
        ]);

        Profile::where('user_id', Auth::id())->increment('total_event_joined', 1);

        $profile->increment('total_expence', $entry_fee);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'type' => 'Entry Fee',
            'message' => '$' . $entry_fee . ' entry fee given.',
            'amount' => $event->entry_fee,
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        Activity::create([
            'date' => Carbon::now()->format('Y-m-d'),
            'user' => 'Player',
            'action' => 'Join Event',
            'details' => 'Join ‘' . $event->title . '’ by paying ' . $entry_fee
        ]);

        $users = User::where('id', '!=', Auth::id())->get();
        $from = Auth::user()->full_name;
        $message = "";

        Auth::user()->notify(new EventJoinNotification('You', $message, $event->title));

        foreach ($users as $user) {
            $user->notify(new EventJoinNotification($from, $message, $event->title));
        }

        return [
            'join' => $join,
            'transaction' => $transaction
        ];
    }
    public function viewEvent($id)
    {
        $event = Event::with([
            'organizer' => function ($q) {
                $q->select('id', 'full_name', 'role', 'avatar');
            }
        ])->where('id', $id)
            ->first();

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

        $event->is_follow = Follow::where('follower_id', $event->organizer_id)->where('user_id', Auth::id())->exists();

        $event->is_join = EventMember::where('event_id', $event->id)
            ->where(function ($q) {
                $q->where('player_id', Auth::id())
                    ->orWhere('team_id', Auth::id());
            })
            ->exists();

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
                    $q->select('id', 'player_id', 'name')
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

        $event->increment('view');

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
    public function showBranches()
    {

        $user = User::find(Auth::id());

        $branches = Branch::where('country', $user->country)->latest()->get();

        return $branches;
    }
}
