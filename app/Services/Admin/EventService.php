<?php

namespace App\Services\Admin;

use App\Models\Event;
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
}
