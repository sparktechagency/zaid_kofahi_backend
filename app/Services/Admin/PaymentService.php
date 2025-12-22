<?php

namespace App\Services\Admin;

use App\Models\Payment;
use App\Models\User;

class PaymentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function paymentList()
    {
        $payment_players = Payment::where('role', 'PLAYER')->latest()->paginate();

        $payment_players->getCollection()->transform(function ($payment) {
            // winners decode
            $winners = json_decode($payment->winners, true);

            if (is_array($winners)) {
                // প্রত্যেক winner এর সাথে player attach করা
                foreach ($winners as &$winner) {
                    $player = User::select('id', 'full_name', 'role')
                        ->find($winner['player_id'] ?? null);

                    $winner['player'] = $player;
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
        $payment = Payment::where('id',$id)->first();

        if($payment->event_type == 'single'){
            return 'single';
        }else{
            return 'team';
        }

        return $payment;
    }
}
