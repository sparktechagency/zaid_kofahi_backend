<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EventEndStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:event-end-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event end status to event over when ending_date is over';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $events = Event::where('status', 'Ongoing')
            ->whereNotNull('ending_date')
            ->where('ending_date', '<', $now)
            ->get();

        foreach ($events as $event) {
            $event->status = 'Event Over';
            $event->save();
        }

        $this->info('Update event end status updated successfully.');
    }
}
