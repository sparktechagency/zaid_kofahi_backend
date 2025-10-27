<?php

namespace App\Http\Controllers\Api\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\CreateEventRequest;
use App\Http\Requests\Organizer\EditEventRequest;
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
        return $this->sendResponse($event, 'Event created successfully.',true,201);
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
            return $this->sendError('Event not found.',[],404);
        }

        return $this->sendResponse($event, 'Event successfully retrieved.');
    }

    public function editEvent(EditEventRequest $request, $id)
    {
        $validatedData = $request->validated();
        $event = $this->eventService->updateEvent($id, $validatedData);

        if (!$event) {
            return $this->sendError('Event not found.',[],404);
        }

        return $this->sendResponse($event, 'Event updated successfully.');
    }

    public function deleteEvent($id)
    {
        $deleted = $this->eventService->deleteEvent($id);

        if (!$deleted) {
            return $this->sendError('Event not found.',[],404);
        }

        return $this->sendResponse([], 'Event deleted successfully.');
    }
}
