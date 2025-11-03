<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\TeamService;
use Exception;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $teamService;
    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }
     public function getTeams()
    {
        try {
            $members = $this->teamService->getTeams();
            return $this->sendResponse($members, 'Get teams successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function viewTeam($id)
    {
        try {
            $event = $this->teamService->viewTeam($id);
            if (!$event) {
                return $this->sendError('Event not found.', [], 404);
            }
            return $this->sendResponse($event, 'Event successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
