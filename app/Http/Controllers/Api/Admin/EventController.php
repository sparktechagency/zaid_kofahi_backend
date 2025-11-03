<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\EventService;
use Exception;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $eventService;
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
     public function getEvents()
    {
        try {
            $members = $this->eventService->getEvents();
            return $this->sendResponse($members, 'Get Events successfully retrieved.');
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

     public function getWinners($id)
    {
        try {
            $winners = $this->eventService->getWinners($id);
            return $this->sendResponse($winners, 'Get winners successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function acceptWinner($id)
    {
        try {
            $accepted = $this->eventService->acceptWinner($id);
            return $this->sendResponse($accepted, 'Winner Accepted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function declineWinner($id)
    {
        try {
            $declined = $this->eventService->declineWinner($id);
            return $this->sendResponse($declined, 'Winner deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function prizeDistribution($id)
    {
        try {
            $prize_distribution = $this->eventService->prizeDistribution($id);
            return $this->sendResponse($prize_distribution, 'Prize distribution successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
