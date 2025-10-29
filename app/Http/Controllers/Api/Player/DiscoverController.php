<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Services\Player\DiscoverService;
use Exception;
use Illuminate\Http\Request;

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
            $events = $this->discoverService->getEvents($request->per_page);
            return $this->sendResponse($events, 'All events successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function singleJoin(Request $request, $id)
    {
        try {
            $event = $this->discoverService->singleJoin($id);
            return $this->sendResponse($event, 'Single join successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function teamJoin(Request $request, $id)
    {
        try {
            $event = $this->discoverService->teamJoin($id, $request->team_id);
            return $this->sendResponse($event, 'Team join successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
