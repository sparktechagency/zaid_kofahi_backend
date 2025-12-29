<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Profile;
use App\Models\Transaction;
use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct()
    {
        //
    }

    public function getTransactions1(?string $filter, ?int $per_page)
    {

        if (in_array(Auth::user()->role, ['ADMIN', 'FINANCE', 'SUPPORT'])) {
            $transactions = Transaction::latest()->paginate($per_page ?? 10);
            $withdraws = Withdraw::latest()->paginate($per_page ?? 10);
            return [
                'withdraw_histories' => $withdraws,
                'transactions_histories' => $transactions
            ];
        }

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
        $available_balance = $profile->total_balance + $profile->total_earning - ($profile->total_expence + $profile->total_withdraw);

        return [
            'available_balance' => $available_balance,
            'transactions_histories' => $transactions
        ];
    }
    public function getTransactions2(?string $filter, ?int $per_page)
    {
        $days = in_array($filter, ['7', '15', '30']) ? (int) $filter : null;

        /* ================= ADMIN / FINANCE / SUPPORT ================= */
        if (in_array(Auth::user()->role, ['ADMIN', 'FINANCE', 'SUPPORT'])) {

            $transactions = Transaction::query()
                ->when($days, function ($q) use ($days) {
                    $q->whereDate('date', '>=', Carbon::now()->subDays($days));
                })
                ->latest()
                ->paginate($per_page ?? 10);

            $withdraws = Withdraw::with('user')->query()
                ->when($days, function ($q) use ($days) {
                    $q->whereDate('date', '>=', Carbon::now()->subDays($days));
                })
                ->latest()
                ->paginate($per_page ?? 10);

            return [
                'withdraw_histories' => $withdraws,
                'transactions_histories' => $transactions
            ];
        }

        /* ================= PLAYER / USER ================= */
        $transactions = Transaction::where('user_id', Auth::id())
            ->when($days, function ($q) use ($days) {
                $q->whereDate('date', '>=', Carbon::now()->subDays($days));
            })
            ->latest()
            ->paginate($per_page ?? 10);

        foreach ($transactions as $transaction) {
            $event = Event::find($transaction->event_id);
            $transaction->event_title = $event ? $event->title : 'Event not found';

            $transaction->date = $transaction->date
                ? Carbon::parse($transaction->date)->format('M d, Y')
                : 'Date not available';
        }

        $profile = Profile::where('user_id', Auth::id())->first();

        $available_balance =
            $profile->total_balance +
            $profile->total_earning -
            ($profile->total_expence + $profile->total_withdraw);

        return [
            'available_balance' => $available_balance,
            'transactions_histories' => $transactions
        ];
    }
    public function getTransactions(?string $filter, ?int $per_page)
    {
        $days = in_array($filter, ['7', '15', '30']) ? (int) $filter : null;

        /* ================= ADMIN / FINANCE / SUPPORT ================= */
        if (in_array(Auth::user()->role, ['ADMIN', 'FINANCE', 'SUPPORT'])) {

            $transactions = Transaction::with('user:id,full_name,user_name')->when($days, function ($q) use ($days) {
                $q->whereDate('date', '>=', Carbon::now()->subDays($days));
            })
                ->latest()
                ->paginate($per_page ?? 10);

            $withdraws = Withdraw::with('user:id,full_name,user_name')
                ->when($days, function ($q) use ($days) {
                    $q->whereDate('date', '>=', Carbon::now()->subDays($days));
                })
                ->latest()
                ->paginate($per_page ?? 10);

            foreach ($transactions as $transaction) {
                $transaction->event_name = Event::where('id', $transaction->event_id)->first()->title ?? null;
            }

            return [
                'withdraw_histories' => $withdraws,
                'transactions_histories' => $transactions
            ];
        }

        /* ================= PLAYER / USER ================= */
        $transactions = Transaction::with('user:id,full_name,user_name')->where('user_id', Auth::id())
            ->when($days, function ($q) use ($days) {
                $q->whereDate('date', '>=', Carbon::now()->subDays($days));
            })
            ->latest()
            ->paginate($per_page ?? 10);

        foreach ($transactions as $transaction) {
            $transaction->event_title = optional(
                Event::find($transaction->event_id)
            )->title ?? 'Event not found';

            $transaction->date = $transaction->date
                ? Carbon::parse($transaction->date)->format('M d, Y')
                : 'Date not available';

        }

        $profile = Profile::where('user_id', Auth::id())->first();

        $available_balance =
            $profile->total_balance +
            $profile->total_earning -
            ($profile->total_expence + $profile->total_withdraw);

        return [
            'available_balance' => $available_balance,
            'transactions_histories' => $transactions
        ];
    }
    public function withdraw($data)
    {
        $profile = Profile::find(Auth::id());

        $available_balance = ($profile->total_balance + $profile->total_earning) - ($profile->total_expence + $profile->total_withdraw);

        if ($data['amount'] > $available_balance) {
            throw ValidationException::withMessages([
                'message' => 'You do not have enough money in your wallet.',
            ]);
        }

        if ($data['amount'] < 100) {
            throw ValidationException::withMessages([
                'message' => 'You cannot withdraw less than $100.',
            ]);
        }

        $withdraw = Withdraw::create([
            'user_id' => Auth::id(),
            'amount' => $data['amount'],
            'date' => Carbon::now()->format('Y-m-d')
        ]);

        return $withdraw;
    }
    public function requestAccept($id)
    {
        $withdraw = Withdraw::where('id', $id)->first();

        if (!$withdraw) {
            throw ValidationException::withMessages([
                'message' => 'Withdraw request id not found.',
            ]);
        }

        if ($withdraw->status == 'Completed') {
            throw ValidationException::withMessages([
                'message' => 'Your are already withdraw request accepted.',
            ]);
        }

        $profile = Profile::where('user_id', $withdraw->user_id)->first();

        $profile->increment('total_withdraw', $withdraw->amount);

        $withdraw->status = 'Completed';
        $withdraw->save();

        $transaction = Transaction::create([
            'payment_intent_id' => '',
            'user_id' => $withdraw->user_id,
            'event_id' => null,
            'type' => 'Withdraw',
            'message' => '$'.$withdraw->amount.' withdraw form your wallet.',
            'amount' => $withdraw->amount,
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        return $transaction;
    }

    public function getWithdrawHistories(?string $filter, ?int $per_page)
    {
        $histories = Withdraw::with('user:id,full_name,user_name,role')->where('user_id',Auth::id())->latest()->get();

        return $histories;
    }

    
}
