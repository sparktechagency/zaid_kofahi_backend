<?php

namespace App\Services\Admin;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct()
    {
        //
    }
    public function paymentList()
    {
        $payment_players = Payment::where('role', 'PLAYER')->latest()->paginate();

        $payment_players->getCollection()->transform(function ($payment) {

            $winners = $payment->winners;

            if (is_string($winners)) {
                $winners = json_decode($winners, true);
            }

            $winners = $winners ?? [];

            if (is_array($winners)) {

                foreach ($winners as &$winner) {

                    if (!empty($winner['player_id'])) {
                        $winner['player'] = User::select('id', 'full_name', 'role')
                            ->find($winner['player_id']);
                    }

                    if (!empty($winner['team_id'])) {
                        $team = Team::with('player:id,full_name,role')
                            ->select('id', 'name', 'player_id')
                            ->find($winner['team_id']);

                        $winner['team'] = $team;
                    }
                }
            }

            $payment->winners = $winners;

            return $payment;
        });

        $payment_organizer = Payment::where('role', 'ORGANIZER')->latest()->paginate();

        return [
            'payment_players' => $payment_players,
            'payment_organizer' => $payment_organizer,
        ];
    }
    public function confirmPayment($id)
    {
        $payment = Payment::where('id', $id)->first();

        if (!$payment) {
            throw ValidationException::withMessages([
                'message' => 'Payment id not found.',
            ]);
        }

        $event = Event::find($payment->event_id);

        if ($payment->role == "ORGANIZER") {
            $organizer_id = $payment->user_id;
            $earning_amount = $payment->amount;
            Profile::where('user_id', $organizer_id)->increment('total_earning', $earning_amount);
            $transaction = Transaction::create([
                'payment_intent_id' => '',
                'user_id' => $organizer_id,
                'event_id' => $payment->event_id,
                'type' => 'Earning',
                'message' => '$' . $earning_amount . ' earning form ' . $event->title,
                'amount' => $earning_amount,
                'data' => Carbon::now()->format('Y-m-d'),
                'status' => 'Completed',
            ]);
            return $transaction;
        }

        $winners = $payment->winners;

        $transactions = [];

        foreach ($winners as $winner) {

            if ($winner['player_id'] != null) {
                $player_id = $winner['player_id'];
            } else {
                $player_id = Team::where('id', $winner['team_id'])->first()->player_id;
            }

            $winning_amount = $winner['amount'];

            Profile::where('user_id', $player_id)->increment('total_earning', $winning_amount);

            $transaction = Transaction::create([
                'payment_intent_id' => '',
                'user_id' => $player_id,
                'event_id' => $payment->event_id,
                'type' => 'Winning',
                'message' => '$' . $winning_amount . ' winning form ' . $event->title,
                'amount' => $winning_amount,
                'data' => Carbon::now()->format('Y-m-d'),
                'status' => 'Completed',
            ]);

            $transactions[] = $transaction;
        }

        return $transactions;
    }

}
