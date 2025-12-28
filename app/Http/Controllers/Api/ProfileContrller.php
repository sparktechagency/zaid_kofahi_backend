<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\CreateReportRequest;
use App\Http\Requests\Player\TeamCreateRequest;
use App\Http\Requests\Player\TeamEditRequest;
use App\Services\ProfileService;
use Exception;
use Illuminate\Http\Request;

class ProfileContrller extends Controller
{
    protected $profileService;
    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }
    public function createTeam(TeamCreateRequest $request)
    {
        try {
            $team = $this->profileService->createTeam($request->validated());
            return $this->sendResponse($team, 'Team created successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getTeams()
    {
        try {
            $teams = $this->profileService->getTeams();
            return $this->sendResponse($teams, 'All teams successfully retrieved.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function viewTeam($id)
    {
        try {
            $team = $this->profileService->viewTeam($id);
            return $this->sendResponse($team, 'Team successfully retrieved.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function editTeam(TeamEditRequest $request, $id)
    {
        try {
            $team = $this->profileService->editTeam($id, $request->validated());
            return $this->sendResponse($team, 'Team updated successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function deleteTeam($id)
    {
        try {
            $this->profileService->deleteTeam($id);
            return $this->sendResponse([], 'Team deleted successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function organizerProfileInfo()
    {
        try {
            $teams = $this->profileService->organizerProfileInfo();
            return $this->sendResponse($teams, 'Organizer profile informantion successfully retrieved.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function playerProfileInfo(Request $request)
    {
        try {
            $teams = $this->profileService->playerProfileInfo($request->user_id);
            return $this->sendResponse($teams, 'Player profile informantion successfully retrieved.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function createReport(CreateReportRequest $request)
    {
        try {
            $teams = $this->profileService->createReport($request->validated());
            return $this->sendResponse($teams, 'Report created successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function getFollowerFollowingList(Request $request)
    {
        try {
            $result = $this->profileService->getFollowerFollowingList($request->search);
            return $this->sendResponse($result, 'Get follower following list successfully retrieved.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function share($id)
    {
        try {
            $team = $this->profileService->share($id);
            return $this->sendResponse($team, 'Event share successfully.', true, 200);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
