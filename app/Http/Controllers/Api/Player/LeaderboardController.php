<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Services\Player\LeaderboardService;
use Exception;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
     protected $leaderboardService;
    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }
     public function leaderboardInfo(Request $request)
    {
        try {
            $members = $this->leaderboardService->leaderboardInfo($request->filter);
            return $this->sendResponse($members, 'Leader board information successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
