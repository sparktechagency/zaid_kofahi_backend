<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Services\Player\DiscoverService;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{

    protected $discoverService;

    // Dependency injection of EventService
    public function __construct(DiscoverService $discoverService)
    {
        $this->discoverService = $discoverService;
    }
    public function getEvents(Request $request)
    {
        $events = $this->discoverService->getEvents($request->per_page);
        return $this->sendResponse($events, 'All events successfully retrieved.');

    }
}
