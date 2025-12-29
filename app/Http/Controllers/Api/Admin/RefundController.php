<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RefundService;
use Exception;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    protected $refundService;
    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }
    public function refundList()
    {
        try {
            $result = $this->refundService->refundList();
            return $this->sendResponse($result, 'Refund list successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function confirmRefund($id)
    {
        try {
            $result = $this->refundService->confirmRefund($id);
            return $this->sendResponse($result, 'Refund confirmed successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function cancelRefund($id)
    {
        try {
            $result = $this->refundService->cancelRefund($id);
            return $this->sendResponse([], 'Refund canceled successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
