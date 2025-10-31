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
}
