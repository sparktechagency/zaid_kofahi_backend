<?php

namespace App\Services\Organizer;

use App\Models\Event;
use App\Models\Profile;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function deposit($data)
    {
        $profile = Profile::where('user_id', Auth::id())->first();

        $profile->increment('total_balance', $data['amount']);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'event_id' => $data['event_id'],
            'type' => 'Deposit',
            'amount' => $data['amount'],
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        return $transaction;

    }

    public function getTransactions(?int $per_page)
    {
        $transactions = Transaction::where('user_id', Auth::id())->latest()->paginate($per_page ?? 10);

        foreach ($transactions as $transaction) {
            $event = Event::where('id', $transaction->event_id)->first();
            $transaction->event_title = $event ? $event->title : 'Event not found';
            if ($transaction->date) {
                $transaction->date = Carbon::parse($transaction->date)->format('M d, Y');
            } else {
                $transaction->date = 'Date not available';
            }
        }

        $profile = Profile::where('user_id', Auth::id())->first();
        $available_balance = $profile->total_balance - ($profile->total_expence + $profile->total_withdraw);

        return [
            'available_balance' => $available_balance,
            'transactions_histories' => $transactions
        ];
    }

}
