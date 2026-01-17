<?php

namespace App\Services\Admin;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Profile;
use App\Models\Refund;
use App\Models\Team;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct()
    {
        //
    }
    public function refundList()
    {
        $refunds = Refund::with(['event:id,organizer_id,title,number_of_player_required,number_of_team_required,number_of_player_required_in_a_team,entry_fee,prize_amount', 'event.organizer:id,full_name,role'])
            ->latest()
            ->paginate();

        foreach ($refunds as $refund) {
            $refund->event_member_count = EventMember::where('event_id', $refund->event_id)->count();
            $refund->event_members = EventMember::with('team.player:id,full_name,role')->where('event_id', $refund->event_id)->get();
        }

        return $refunds;
    }
    public function confirmRefund($id)
    {
        $refund = Refund::find($id);


        if (!$refund) {
            throw ValidationException::withMessages([
                'message' => 'Refund id not found.',
            ]);
        }

        if ($refund->status == 'Completed') {
            throw ValidationException::withMessages([
                'message' => 'Refund already completed.',
            ]);
        }

        $event = Event::find($refund->event_id);

        $entry_fee = $event->entry_fee;
        $prize_amount = $event->prize_amount;

        $event_members = EventMember::where('event_id', $event->id)->get();

        if ($refund->event_type == 'single') {
            foreach ($event_members as $member) {
                $player_id = $member->player_id;

                Profile::where('user_id', $player_id)->increment('total_balance', $entry_fee);
                $player_transaction = Transaction::create([
                    'payment_intent_id' => '',
                    'user_id' => $player_id,
                    'event_id' => $event->id,
                    'type' => 'Refund',
                    'message' => '$' . $entry_fee . ' refund form ' . $event->title,
                    'amount' => $entry_fee,
                    'data' => Carbon::now()->format('Y-m-d'),
                    'status' => 'Completed',
                ]);
            }
        } else {
            foreach ($event_members as $member) {
                $player_id = Team::where('id', $member->team_id)->first()->player_id;

                Profile::where('user_id', $player_id)->increment('total_balance', $entry_fee);
                $player_transaction = Transaction::create([
                    'payment_intent_id' => '',
                    'user_id' => $player_id,
                    'event_id' => $event->id,
                    'type' => 'Refund',
                    'message' => '$' . $entry_fee . ' refund form ' . $event->title,
                    'amount' => $entry_fee,
                    'data' => Carbon::now()->format('Y-m-d'),
                    'status' => 'Completed',
                ]);
            }
        }

        Profile::where('user_id', $event->organizer_id)->increment('total_balance', $prize_amount);
        $organizer_transaction = Transaction::create([
            'payment_intent_id' => '',
            'user_id' => $event->organizer_id,
            'event_id' => $event->id,
            'type' => 'Refund',
            'message' => '$' . $prize_amount . ' refund form ' . $event->title,
            'amount' => $prize_amount,
            'data' => Carbon::now()->format('Y-m-d'),
            'status' => 'Completed',
        ]);

        $refund->status = 'Completed';
        $refund->save();

        return [
            'player_transaction' => $player_transaction ?? [],
            'organizer_transaction' => $organizer_transaction ?? [],
        ];
    }
    
    public function cancelRefund($id)
    {
        $refund = Refund::find($id);

        if (!$refund) {
            throw ValidationException::withMessages([
                'message' => 'Refund id not found.',
            ]);
        }

        return $refund->delete();
    }
}
