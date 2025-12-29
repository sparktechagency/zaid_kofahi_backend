<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DisputeService;
use Exception;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    protected $disputeService;
    public function __construct(DisputeService $disputeService)
    {
        $this->disputeService = $disputeService;
    }
    public function getDisputes()
    {
        try {
            $result = $this->disputeService->getDisputes();
            return $this->sendResponse($result, 'Get disputes successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function reportSolve($id)
    {
        try {
            $result = $this->disputeService->reportSolve($id);
            return $this->sendResponse($result, 'Player report solved successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
