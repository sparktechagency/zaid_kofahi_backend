<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\EarningService;
use Exception;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    protected $earningService;
    public function __construct(EarningService $earningService)
    {
        $this->earningService = $earningService;
    }
    public function earningList()
    {
        try {
            $result = $this->earningService->earningList();
            return $this->sendResponse($result, 'Earning list successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
