<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventJoinNotification;
use App\Services\Player\DiscoverService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscoverController extends Controller
{
    protected $discoverService;
    public function __construct(DiscoverService $discoverService)
    {
        $this->discoverService = $discoverService;
    }
    public function getEvents(Request $request)
    {
        try {
            $events = $this->discoverService->getEvents($request->per_page, $request->search, $request->filter);
            return $this->sendResponse($events, 'All events successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function singleJoin(Request $request, $id)
    {
        try {
            $event = $this->discoverService->singleJoin($request->player_id, $id);


            $users = User::where('id','!=',Auth::id())->get();

            $from = Auth::user()->full_name;
            $message = "";

            $event = Event::find($id);

            Auth::user()->notify(new EventJoinNotification('You', $message, $event->title));

            foreach ($users as $user) {
                $user->notify(new EventJoinNotification($from, $message, $event->title));
            }


            return $this->sendResponse($event, 'Single join successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function teamJoin(Request $request, $id)
    {
        try {
            $event = $this->discoverService->teamJoin($id, $request->team_id);

            $users = User::where('id','!=',Auth::id())->get();

            $from = Auth::user()->full_name;
            $message = "";

            $event = Event::find($id);

            Auth::user()->notify(new EventJoinNotification('You', $message, $event->title));

            foreach ($users as $user) {
                $user->notify(new EventJoinNotification($from, $message, $event->title));
            }

            return $this->sendResponse($event, 'Team join successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function viewEvent($id)
    {
        try {
            $event = $this->discoverService->viewEvent($id);
            if (!$event) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse($event, 'Event successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getEventDetails($id)
    {
        try {
            $event = $this->discoverService->getEventDetails($id);
            if (!$event) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse($event, 'Event details successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function createCashRequest(Request $request, $id)
    {
        try {

            $data = [
                'event_id' => $id,
                'player_id' => Auth::id(),
                'team_id' => $request->team_id,
                'amount' => $request->amount,
                'branch_id' => $request->branch_id,
            ];

            $event = $this->discoverService->createCashRequest($data);

            return $this->sendResponse($event, 'Cash request created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function showBranches(Request $request)
    {
        try {
            $events = $this->discoverService->showBranches();
            return $this->sendResponse($events, 'Show branches successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

}
