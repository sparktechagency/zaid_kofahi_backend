<?php

namespace App\Http\Controllers\Api\Organizer;

use App\Http\Controllers\Controller;
use App\Services\Organizer\PerformanceService;
use Exception;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
     protected $performanceService;
    public function __construct(PerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }
     public function performanceInfo()
    {
        try {
            $members = $this->performanceService->performanceInfo();
            return $this->sendResponse($members, 'Performance information successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
