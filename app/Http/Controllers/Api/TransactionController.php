<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected $transactionService;
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    public function getTransactions(Request $request)
    {
        try {
            $events = $this->transactionService->getTransactions($request->filter, $request->per_page);
            return $this->sendResponse($events, in_array(Auth::user()->role, ['ADMIN']) ? 'All withdraws and transactions successfully retrieved.' : 'Your all transactions successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function withdraw(WithdrawRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $transaction = $this->transactionService->withdraw($validatedData);
            return $this->sendResponse($transaction, 'Withdraw request to admin successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function requestAccept($id)
    {
        try {
            $transaction = $this->transactionService->requestAccept($id);
            return $this->sendResponse($transaction, 'Withdraw request accepted successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getWithdrawHistories(Request $request)
    {
        try {
            $events = $this->transactionService->getWithdrawHistories($request->filter, $request->per_page);
            return $this->sendResponse($events, 'Get your withdraw histories successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
