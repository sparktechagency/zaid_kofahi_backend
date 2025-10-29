<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\TeamCreateRequest;
use App\Http\Requests\Player\TeamEditRequest;
use App\Services\Player\MyProfileService;
use Exception;
use Illuminate\Http\Request;

class MyProfileContrller extends Controller
{
    protected $myProfileService;
    public function __construct(MyProfileService $myProfileService)
    {
        $this->myProfileService = $myProfileService;
    }
    public function createTeam(TeamCreateRequest $request)
    {
        try {
            $team = $this->myProfileService->createTeam($request->validated());
            return $this->sendResponse($team, 'Team created successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getTeams()
    {
        try {
            $teams = $this->myProfileService->getTeams();
            return $this->sendResponse($teams, 'All teams successfully retrieved.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function viewTeam($id)
    {
        try {
            $team = $this->myProfileService->viewTeam($id);
            return $this->sendResponse($team, 'Team successfully retrieved.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function editTeam(TeamEditRequest $request, $id)
    {
        try {
            $team = $this->myProfileService->editTeam($id, $request->validated());
            return $this->sendResponse($team, 'Team updated successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function deleteTeam($id)
    {
        try {
            $this->myProfileService->deleteTeam($id);
            return $this->sendResponse([], 'Team deleted successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
