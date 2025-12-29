<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\LeadBoardService;
use Exception;
use Illuminate\Http\Request;

class LeaderBoardController extends Controller
{
    protected $leadBoardService;
    public function __construct(LeadBoardService $leadBoardService)
    {
        $this->leadBoardService = $leadBoardService;
    }
    public function leaderBoardInfo()
    {
        try {
            $result = $this->leadBoardService->leaderBoardInfo();
            return $this->sendResponse($result, 'Leader board information successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
