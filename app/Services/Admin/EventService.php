<?php

namespace App\Services\Admin;

use App\Models\Earning;
use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use App\Models\Winner;
use Carbon\Carbon;

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
        $events = Event::latest()->get();

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

    public function getWinners($id)
    {
        $events = Winner::where('event_id', $id)->latest()->get();
        return $events;
    }

    public function acceptWinner($id)
    {
        $winner = Winner::where('id', $id)->first();

        $winner->admin_approval = true;
        $winner->status = 'Accepted';
        $winner->save();

        return $winner;
    }

    public function declineWinner($id)
    {
        $winner = Winner::where('id', $id)->first();

        $winner->admin_approval = false;
        $winner->status = 'Decline';
        $winner->save();

        return $winner;
    }

    //  $table->unsignedInteger('user_id');
    //         $table->enum('role', ['PLAYER', 'ORGANIZER']);
    //         $table->unsignedInteger('event_id');
    //         $table->string('event_name');
    //         $table->enum('event_type', ['single', 'team']);
    //         $table->json('winners')->nullable();
    //         $table->string('organizer')->nullable();
    //         $table->decimal('amount', 10, 2)->default(0);
    //         $table->date('date');
    //         $table->enum('status', ['Pending', 'Completed'])->default('Pending');

    public function prizeDistribution($id)
    {

        $event = Event::where('id', $id)->first();

        $winners = Winner::where('event_id', $event->id)->where('status', 'Accepted')->get();

        $organizer = User::where('id', $event->organizer_id)->first();

        if ($event) {
            $event->status = 'Completed';
            $event->save();
        }

        if ($event->sport_type == 'single') {

            Earning::create([
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'total_entries' => $event->number_of_player_required * $event->entry_fee,
                'commission' => ($event->number_of_player_required * $event->entry_fee) * 0.1,
            ]);

            Payment::create([
                'user_id' => $organizer->id,
                'role' => $organizer->role,
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'organizer' => $organizer->full_name,
                'amount' => ($event->number_of_player_required * $event->entry_fee) * 0.9,
                'date' => Carbon::now()->format('Y-m-d')
            ]);


            Payment::create([
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'winners' => $winners,
                'amount' => $event->prize_amount,
                'date' => Carbon::now()->format('Y-m-d')
            ]);

        } else {
            Earning::create([
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'total_entries' => $event->number_of_team_required * $event->entry_fee,
                'commission' => ($event->number_of_team_required * $event->entry_fee) * 0.1,
            ]);

            Payment::create([
                'user_id' => $organizer->id,
                'role' => $organizer->role,
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'organizer' => $organizer->full_name,
                'amount' => ($event->number_of_team_required * $event->entry_fee) * 0.9,
                'date' => Carbon::now()->format('Y-m-d')
            ]);


            Payment::create([
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_type' => $event->sport_type,
                'winners' => $winners,
                'amount' => $event->prize_amount,
                'date' => Carbon::now()->format('Y-m-d')
            ]);
        }
    }
}
