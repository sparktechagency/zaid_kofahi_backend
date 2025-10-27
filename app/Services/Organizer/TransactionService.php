<?php

namespace App\Services\Organizer;

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
        $profile = Profile::where('user_id',Auth::id())->first();

        $profile->increment('total_balance',$data['amount']);

        $transaction = Transaction::create([
            'slug' => Str::random(),
            'user_id' => Auth::id(),
            'event_id' => $data['event_id'],
            'type' => 'Deposit',
            'amount' => $data['amount'],
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        return $transaction;
        
    }
}
