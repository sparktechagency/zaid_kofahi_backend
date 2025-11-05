<?php

namespace App\Services\Organizer;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Follow;
use App\Models\Profile;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PerformanceService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function performanceInfo()
    {
        $follower_list = Follow::where('follower_id', Auth::id())->whereHas('user', function ($q) {
            $q->where('role', 'PLAYER');
        })->count();

        $events = Event::where('organizer_id', Auth::id())->get();

        $share = 0;
        foreach ($events as $event) {
            $share += $event->share;
        }

        // Last 30 days
        $last_30_days = Carbon::now()->subDays(30);

        // total
        $single_event_ids = Event::where('organizer_id', Auth::id())
            ->where('sport_type', 'single')
            ->where('created_at', '>=', $last_30_days)
            ->pluck('id');
        $single_joined = EventMember::whereIn('event_id', $single_event_ids)->count();

        $team_event_ids = Event::where('organizer_id', Auth::id())
            ->where('sport_type', 'team')
            ->where('created_at', '>=', $last_30_days)
            ->pluck('id');
        $team_ids = EventMember::whereIn('event_id', $team_event_ids)->pluck('team_id');
        $team_members = TeamMember::whereIn('team_id', $team_ids)->count();

        // active
        $active_single_event_ids = Event::where('organizer_id', Auth::id())
            ->where('sport_type', 'single')->where('status', 'Ongoing')
            ->where('created_at', '>=', $last_30_days)
            ->pluck('id');
        $active_single_joined = EventMember::whereIn('event_id', $active_single_event_ids)->count();

        $active_team_event_ids = Event::where('organizer_id', Auth::id())
            ->where('sport_type', 'team')->where('status', 'Ongoing')
            ->where('created_at', '>=', $last_30_days)
            ->pluck('id');
        $active_team_ids = EventMember::whereIn('event_id', $active_team_event_ids)->pluck('team_id');
        $active_team_members = TeamMember::whereIn('team_id', $active_team_ids)->count();

        // conpleted
        $completed_events = Event::where('organizer_id', Auth::id())->where('status', 'Completed')->count();

        return [
            'account_reach' => $follower_list,
            'social_shares' => $share,
            'total_participants_last_30_days' => $single_joined + $team_members,
            'active_participants_last_30_days' => $active_single_joined + $active_team_members,
            'completed_events' => $completed_events,
        ];

    }
}
