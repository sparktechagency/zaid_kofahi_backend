<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EventStartOrCancelStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:event-start-or-cancel-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event start/cancel status to ongoing/cancelled when starting_date is over';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $events = Event::where('status', 'Upcoming')
            ->whereNotNull('starting_date')
            ->where('starting_date', '<', $now)
            ->get();

        foreach ($events as $event) {

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
                $event->status = 'Ongoing';
                $event->save();
            } else {
                $event->status = 'Cancelled';
                $event->save();

                Refund::create([
                    'event_id' => $event->id,
                    'event_name' => $event->title,
                    'event_type' => $event->sport_type,
                    'participants' => $joined,
                    'total_refund_amount' => $event->entry_fee * $joined,
                    'status' => 'Pending'
                ]);
            }
        }

        $this->info('Update event start/cancel status updated successfully.');
    }
}
