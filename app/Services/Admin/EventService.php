<?php

namespace App\Services\Admin;

use App\Models\Earning;
use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EventService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getEvents()
    {
        $events = Event::with('organizer:id,full_name,user_name')->latest()->get();

        foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }
        return $events;
    }
    public function viewEvent($id)
    {
        $event = Event::with('organizer:id,full_name,user_name')
            ->where('id', $id)
            ->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event id not found.',
            ]);
        }

        $event->prize_distribution = json_decode($event->prize_distribution);
        $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');

        return $event;
    }
    public function getWinners($id)
    {
        $events = Winner::where('event_id', $id)->latest()->get();
        return $events;
    }
    public function acceptWinner($id)
    {
        $winner = Winner::where('id', $id)->first();

        if (!$winner) {
            throw ValidationException::withMessages([
                'message' => 'Winner id not found.',
            ]);
        }

        $winner->admin_approval = true;
        $winner->status = 'Accepted';
        $winner->save();

        return $winner;
    }
    public function declineWinner($id)
    {
        $winner = Winner::where('id', $id)->first();

        if (!$winner) {
            throw ValidationException::withMessages([
                'message' => 'Winner id not found.',
            ]);
        }

        $winner->admin_approval = false;
        $winner->status = 'Decline';
        $winner->save();

        return $winner;
    }
    public function prizeDistribution($id)
    {
        $event = Event::where('id', $id)->first();

        if (!$event) {
            throw ValidationException::withMessages([
                'message' => 'Event id not found.',
            ]);
        }

        $winners = Winner::where('event_id', $event->id)->where('status', 'Accepted')->get();

        $organizer = User::where('id', $event->organizer_id)->first();

        if ($event) {
            $event->status = 'Completed';
            $event->save();
        }

        if ($event->sport_type == 'single') {

            $admin_earning = Earning::create([
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'total_entries' => $event->number_of_player_required * $event->entry_fee,
                'commission' => ($event->number_of_player_required * $event->entry_fee) * 0.1,
            ]);

            $organizer_payment = Payment::create([
                'user_id' => $organizer->id,
                'role' => $organizer->role,
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'organizer' => $organizer->full_name,
                'amount' => ($event->number_of_player_required * $event->entry_fee) * 0.9,
                'date' => Carbon::now()->format('Y-m-d')
            ]);


            $player_payment = Payment::create([
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'winners' => $winners,
                'amount' => $event->prize_amount,
                'date' => Carbon::now()->format('Y-m-d')
            ]);

        } else {
            $admin_earning = Earning::create([
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'total_entries' => $event->number_of_team_required * $event->entry_fee,
                'commission' => ($event->number_of_team_required * $event->entry_fee) * 0.1,
            ]);

            $organizer_payment = Payment::create([
                'user_id' => $organizer->id,
                'role' => $organizer->role,
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'organizer' => $organizer->full_name,
                'amount' => ($event->number_of_team_required * $event->entry_fee) * 0.9,
                'date' => Carbon::now()->format('Y-m-d')
            ]);

            $player_payment = Payment::create([
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'winners' => $winners,
                'amount' => $event->prize_amount,
                'date' => Carbon::now()->format('Y-m-d')
            ]);
        }

        return [
            'admin_earning' => $admin_earning,
            'organizer_payment' => $organizer_payment,
            'player_payment' => $player_payment,
        ];
    }
}
