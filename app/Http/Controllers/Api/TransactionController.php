<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\DepositRequest;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    public function deposit(DepositRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $transaction = $this->transactionService->deposit($validatedData);
            return $this->sendResponse($transaction, 'Deposit successfully.', true, 201);
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
    public function getTransactions(Request $request)
    {
        try {
            $events = $this->transactionService->getTransactions($request->per_page);
            return $this->sendResponse($events, 'Your all transactions successfully retrieved.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong!', ['error' => $e->getMessage()], 500);
        }
    }
}
