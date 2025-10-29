<?php

namespace App\Http\Controllers\Api\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\CreateEventRequest;
use App\Http\Requests\Organizer\EditEventRequest;
use App\Http\Requests\Organizer\EventPayRequest;
use App\Http\Requests\Organizer\SelectedWinnerRequest;
use App\Services\Organizer\EventService;
use Exception;
use Illuminate\Http\Request;

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
            return $this->sendResponse($event, 'Event created successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getEvents(Request $request)
    {
        try {
            $events = $this->eventService->getEvents($request->per_page);
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
    public function eventPay(EventPayRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $event = $this->eventService->eventPay($validatedData, $id);
            if ($event == false) {
                return $this->sendResponse([], 'Insufficient balance!', false, 400);
            }
            return $this->sendResponse($event, 'Event payment successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function selectedWinner(SelectedWinnerRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $event = $this->eventService->selectedWinner($validatedData);
            return $this->sendResponse($event, 'Event winner selected successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
