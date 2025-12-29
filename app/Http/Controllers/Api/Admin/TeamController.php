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
            $result = $this->teamService->getTeams();
            return $this->sendResponse($result, 'Get teams successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function viewTeam($id)
    {
        try {
            $result = $this->teamService->viewTeam($id);
            if (!$result) {
                return $this->sendError('Team not found.', [], 404);
            }
            return $this->sendResponse($result, 'Team successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
