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
            $result = $this->eventService->getEvents();
            return $this->sendResponse($result, 'Get Events successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function viewEvent($id)
    {
        try {
            $result = $this->eventService->viewEvent($id);
            if (!$result) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse($result, 'Event successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

     public function getWinners($id)
    {
        try {
            $result = $this->eventService->getWinners($id);
            return $this->sendResponse($result, 'Get winners successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function acceptWinner($id)
    {
        try {
            $result = $this->eventService->acceptWinner($id);
            return $this->sendResponse($result, 'Winner Accepted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function declineWinner($id)
    {
        try {
            $result = $this->eventService->declineWinner($id);
            return $this->sendResponse($result, 'Winner deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function prizeDistribution($id)
    {
        try {
            $result = $this->eventService->prizeDistribution($id);
            return $this->sendResponse($result, 'Prize distribution successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
