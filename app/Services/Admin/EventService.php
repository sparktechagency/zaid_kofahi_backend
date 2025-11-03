<?php

namespace App\Services\Admin;

use App\Models\Event;
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

    public function prizeDistribution($id)
    {

        $event = Event::where('id', $id)->first();

        if ($event->sport_type == 'single') {
            $winners = Winner::where('event_id', $id)->get();
            return $winners;
        }else{
            $winners = Winner::where('event_id', $id)->get();
            return $winners;
        }
    }
}
