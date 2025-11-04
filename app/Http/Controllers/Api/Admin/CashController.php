<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CashServece;
use Exception;
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
            $event = $this->cashServece->getCashRequests();
            return $this->sendResponse($event, 'Get cash requests successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function viewRequest($id)
    {
        try {
            $event = $this->cashServece->viewRequest($id);
            return $this->sendResponse($event, 'Request successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }

    public function cashVerification($id)
    {
        try {
            $event = $this->cashServece->cashVerification($id);
            return $this->sendResponse($event, 'Cash request verified successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
