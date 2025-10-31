<?php

namespace App\Services\Player;

use App\Models\Event;
use Carbon\Carbon;

class NearMeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function nearMeEvents()
    {

        $events = Event::where('status','!=','Pending Payment')->get();
         foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }

        return $events;

    }
}
