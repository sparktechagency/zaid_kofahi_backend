<?php

namespace App\Services\Player;

use App\Models\Event;
use App\Models\EventMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $events = Event::where('status', '!=', 'Pending Payment')->latest()->paginate($per_page ?? 10);

        foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }

        return $events;
    }

    public function singleJoin($id)
    {
        $join = EventMember::create([
            'player_id' => Auth::id(),
            'event_id' => $id,
            'joining_date' => Carbon::now()->format('Y-m-d')
        ]);

        return $join;
    }
}
