<?php

namespace App\Http\Controllers\Api\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\DepositRequest;
use App\Services\Organizer\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;

    // Dependency injection of EventService
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function deposit(DepositRequest $request)
    {
        $validatedData = $request->validated();
        $transaction = $this->transactionService->deposit($validatedData);
        return $this->sendResponse($transaction, 'Deposit successfully.',true,201);
    }

    public function getTransactions(Request $request)
    {
        $events = $this->transactionService->getTransactions($request->per_page);
        return $this->sendResponse($events, 'Your all transactions successfully retrieved.');

    }
}
