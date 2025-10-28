<?php

namespace App\Http\Controllers\Api\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\CreateEventRequest;
use App\Http\Requests\Organizer\EditEventRequest;
use App\Http\Requests\Organizer\EventPayRequest;
use App\Services\Organizer\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $eventService;

    // Dependency injection of EventService
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function createEvent(CreateEventRequest $request)
    {
        $validatedData = $request->validated(); // Get validated data
        $event = $this->eventService->createEvent($validatedData);
        return $this->sendResponse($event, 'Event created successfully.', true, 201);
    }

    public function getEvents(Request $request)
    {
        $events = $this->eventService->getEvents($request->per_page);
        return $this->sendResponse($events, 'All events successfully retrieved.');

    }

    public function viewEvent($id)
    {
        $event = $this->eventService->viewEvent($id);

        if (!$event) {
            return $this->sendError('Event not found.', [], 404);
        }

        return $this->sendResponse($event, 'Event successfully retrieved.');
    }

    public function editEvent(EditEventRequest $request, $id)
    {
        $validatedData = $request->validated();
        $event = $this->eventService->updateEvent($id, $validatedData);

        if (!$event) {
            return $this->sendError('Event not found.', [], 404);
        }

        return $this->sendResponse($event, 'Event updated successfully.');
    }

    public function deleteEvent($id)
    {
        $deleted = $this->eventService->deleteEvent($id);

        if (!$deleted) {
            return $this->sendError('Event not found.', [], 404);
        }

        return $this->sendResponse([], 'Event deleted successfully.');
    }

    public function getEventDetails($id)
    {
        $event = $this->eventService->getEventDetails($id);

        if (!$event) {
            return $this->sendError('Event not found.', [], 404);
        }

        return $this->sendResponse($event, 'Event details successfully retrieved.');
    }

    public function eventPay(EventPayRequest $request, $id)
    {
        $validatedData = $request->validated();
        $event = $this->eventService->eventPay($validatedData, $id);

        if ($event == false) {
            return $this->sendResponse([], 'Insufficient balance!', false, 400);  // Bad Request for insufficient balance
        }

        return $this->sendResponse($event, 'Event payment successfully.', true, 200);  // 200 OK for successful payment
    }
}
