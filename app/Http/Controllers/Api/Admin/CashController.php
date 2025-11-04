<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CashServece;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashController extends Controller
{
    protected $cashServece;
    public function __construct(CashServece $cashServece)
    {
        $this->cashServece = $cashServece;
    }

    public function getCashRequests()
    {
        try {
            $result = $this->cashServece->getCashRequests();
            return $this->sendResponse($result, 'Get cash requests successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function cashVerification($id)
    {
        try {
            $result = $this->cashServece->cashVerification($id);
            return $this->sendResponse($result, 'Cash request verified successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function deleteRequest($id): JsonResponse
    {
        try {
            $result = $this->cashServece->deleteRequest($id);
            return $this->sendResponse([], 'Cash request deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
