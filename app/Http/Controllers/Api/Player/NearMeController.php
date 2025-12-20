<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Services\Player\NearMeService;
use Exception;
use Illuminate\Http\Request;

class NearMeController extends Controller
{
    protected $nearMeService;
    public function __construct(NearMeService $nearMeService)
    {
        $this->nearMeService = $nearMeService;
    }
    public function nearMeEvents(Request $request)
    {
        try {
            $events = $this->nearMeService->nearMeEvents($request->latitude, $request->longitude,$request->limit,$request->search);
            return $this->sendResponse($events, 'Get nearest events fetched successfully');
        } catch (Exception $e) {
            return $this->sendError('Failed to fetch nearest events', ['error' => $e->getMessage()], 500);
        }
    }
}
