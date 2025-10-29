<?php

namespace App\Services\Player;

use App\Models\Event;
use App\Models\EventMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        $alreadyJoined = EventMember::where('event_id', $id)
            ->where('player_id', Auth::id())
            ->exists();

        if ($alreadyJoined) {
            throw ValidationException::withMessages([
                'message' => 'You are already joined in this event.',
            ]);
        }

        $join = EventMember::create([
            'slug' => Str::random(12),
            'player_id' => Auth::id(),
            'event_id' => $id,
            'joining_date' => Carbon::today(),
        ]);

        return $join;
    }
}
