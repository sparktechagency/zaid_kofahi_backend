<?php

namespace App\Http\Controllers\Api\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\CreateEventRequest;
use App\Http\Requests\Organizer\EditEventRequest;
use App\Http\Requests\Organizer\EventPayRequest;
use App\Http\Requests\Organizer\SelectedWinnerRequest;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Team;
use App\Models\User;
use App\Notifications\EventCreateNotification;
use App\Notifications\KickOutNotification;
use App\Notifications\SelectedWinnerNotification;
use App\Services\Organizer\EventService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected $eventService;
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
    public function createEvent(CreateEventRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $event = $this->eventService->createEvent($validatedData);

            $players = User::where('id', '!=', Auth::id())->get();

            $from = Auth::user()->full_name;
            $message = "";

            Auth::user()->notify(new EventCreateNotification('You', $message));

            foreach ($players as $player) {
                $player->notify(new EventCreateNotification($from, $message));
            }

            return $this->sendResponse($event, 'Event created successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getEvents(Request $request)
    {
        try {
            $events = $this->eventService->getEvents($request->per_page, $request->search, $request->filter);
            return $this->sendResponse($events, 'All events successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function viewEvent($id)
    {
        try {
            $event = $this->eventService->viewEvent($id);
            if (!$event) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse($event, 'Event successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function editEvent(EditEventRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $event = $this->eventService->updateEvent($id, $validatedData);
            if (!$event) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse($event, 'Event updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function deleteEvent($id)
    {
        try {
            $deleted = $this->eventService->deleteEvent($id);
            if (!$deleted) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse([], 'Event deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getEventDetails($id)
    {
        try {
            $event = $this->eventService->getEventDetails($id);
            if (!$event) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse($event, 'Event details successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function selectedWinner(Request $request, $id)
    {
        try {
            $event = $this->eventService->selectedWinner($request->winners, $id);
            return $this->sendResponse($event, 'Event winner(s) selected successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function remove1(Request $request, $id)
    {
        try {
            $remove = $this->eventService->remove($id, $request->event_id);

            $event_member = EventMember::where('id', $id)->first();

            $from = Auth::user()->full_name;
            $message = "";

            $event = Event::find($request->event_id);

            if ($event_member->player_id == null) {

                $team_owner = Team::where('id', $event_member->team_id)->first()->player_id;

                User::where('id', $team_owner)->first()->notify(new KickOutNotification($from, $message, $event->title));

            } else {
                $player = User::find($event_member->player_id);
                $player->notify(new KickOutNotification($from, $message, $event->title));
            }

            return $this->sendResponse([$remove], 'Event member remove successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function remove(Request $request, $id)
    {
        try {

            $event_member = EventMember::find($id);

            if (!$event_member) {
                return $this->sendError('Member not found', [], 404);
            }

            $from = Auth::user()->full_name;
            $message = "";

            $event = Event::find($request->event_id);

            if ($event_member->player_id === null) {

                $team = Team::find($event_member->team_id);

                if ($team && $team->player_id) {
                    $owner = User::find($team->player_id);

                    if ($owner) {
                        $owner->notify(new KickOutNotification($from, $message, $event?->title));
                    }
                }

            } else {

                $player = User::find($event_member->player_id);

                if ($player) {
                    $player->notify(new KickOutNotification($from, $message, $event?->title));
                }
            }

            $remove = $this->eventService->remove($id, $request->event_id);

            return $this->sendResponse([$remove], 'Event member removed successfully.');

        } catch (Exception $e) {

            return $this->sendError(
                'Something went wrong!',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
    public function getEventMembersList($id)
    {
        try {
            $members = $this->eventService->getEventMembersList($id);
            return $this->sendResponse($members, 'Event members successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function eventPay($id)
    {
        try {
            $result = $this->eventService->eventPay($id);
            return $this->sendResponse($result, 'Event pay successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
