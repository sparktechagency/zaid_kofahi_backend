<?php

namespace App\Services\Player;

use App\Models\Event;
use Carbon\Carbon;

class DiscoverService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        
    }

    public function getEvents(?int $per_page)
    {
        $events = Event::where('status','!=','Pending Payment')->latest()->paginate($per_page ?? 10);

        foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }

        return $events;
    }
}
