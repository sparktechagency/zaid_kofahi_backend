<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Exception;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
     public function dashboardInfo()
    {
        try {
            $members = $this->dashboardService->dashboardInfo();
            return $this->sendResponse($members, 'Dashboard information successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
