<?php

namespace App\Services\Organizer;

use App\Models\Event;
use App\Models\Profile;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventService
{
    /**
     * Create a new class instance.
     */
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

        $data['time'] = Carbon::createFromFormat('h:i A', $data['time'])->format('H:i');  // 15:00

        return Event::create($data);
    }
    public function updateEvent($id, $data)
    {
        $event = Event::where('slug', $id)
            ->orWhere('id', $id)
            ->first();

        if ($event) {
            $event = Event::where('slug', $id)
                ->orWhere('id', $id)
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
            $event->entry_type = $data['entry_type'] ?? $event->entry_type;
            $event->starting_date = $data['starting_date'] ?? $event->starting_date;
            $event->ending_date = $data['ending_date'] ?? $event->ending_date;
            $event->time = $data['time'] ?? $event->time;
            $event->location = $data['location'] ?? $event->location;
            $event->number_of_player_required = $data['number_of_player_required'] ?? $event->number_of_player_required;
            $event->entry_free = $data['entry_free'] ?? $event->entry_free;
            $event->prize_amount = $data['prize_amount'] ?? $event->prize_amount;
            $event->prize_distribution = $data['prize_distribution'] ?? $event->prize_distribution;
            $event->rules_guidelines = $data['rules_guidelines'] ?? $event->rules_guidelines;
            $event->save();

            return $event;
        }

        return null;
    }
    public function getEvents(?int $per_page)
    {
        $events = Event::latest()->paginate($per_page ?? 10);
        foreach ($events as $event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }
        return $events;
    }
    public function viewEvent($id)
    {
        $event = Event::where('slug', $id)
            ->orWhere('id', $id)
            ->first();

        $event->prize_distribution = json_decode($event->prize_distribution);
        $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');

        return $event;
    }
    public function deleteEvent($id)
    {
        $event = Event::where('slug', $id)
            ->orWhere('id', $id)
            ->first();

        if ($event && $event->status == 'Pending Payment') {
            $event->delete();
            return true;
        }

        return false;
    }
    public function getEventDetails($id)
    {
        $event = Event::where('slug', $id)
            ->orWhere('id', $id)
            ->select('id', 'slug', 'title', 'starting_date', 'ending_date', 'time', 'location', 'prize_amount', 'prize_distribution', 'image')
            ->first();

        if ($event) {
            $event->prize_distribution = json_decode($event->prize_distribution);
            $event->time = Carbon::createFromFormat('H:i:s', $event->time)->format('h:i A');
        }

        return [
            'event' => $event,
            'joined_players' => 'joined players',
            'top_3_winners' => 'top 3 winners',
            'event_status' => 'event status'
        ];
    }
    public function eventPay($data, $id)
    {
        $profile = Profile::where('user_id', Auth::id())->first();

        $available_balance = $profile->total_balance - ($profile->total_expence + $profile->total_withdraw);

        if ($available_balance >= $data['amount']) {
            $profile->increment('total_expence', $data['amount']);

            $event = Event::where('id', $id)
                ->orWhere('slug', $id)
                ->first();

            $event->status = 'Upcoming';
            $event->save();

            $transaction = Transaction::create([
                'slug' => Str::random(),
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'type' => 'Payout',
                'amount' => $data['amount'],
                'data' => Carbon::now()->format('Y-m-d'),
                'status' => 'Completed',
            ]);

            return $transaction;
        } else {
            return false;
        }
    }
}
