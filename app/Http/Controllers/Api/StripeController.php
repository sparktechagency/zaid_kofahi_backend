<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function createConnectedAccount(Request $request)
    {
        $email = Auth::user()->email;

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $account = Account::create([
                'type' => 'express',
                'country' => 'US',
                // 'email' => $request->email,
                'email' => $email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
            ]);

            $customReturnUrl = url("/connected?status=success&email={$email}&account_id={$account->id}");

            $accountLink = AccountLink::create([
                'account' => $account->id,
                'refresh_url' => url('/vendor/reauth'),
                'return_url' => $customReturnUrl,
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stripe Connect account created successfully',
                'onboarding_url' => $accountLink->url,
                'stripe_account_id' => $account->id,
            ]);
        } catch (Exception $e) {
            Log::error('Stripe Account Creation Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function handleConnectedAccount(Request $request)
    {
        $email = $request->email;
        $accountId = $request->account_id;

        if (!$email || !$accountId) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required parameters.'
            ], 400);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $account = Account::retrieve($accountId);

            if (!$account->charges_enabled) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stripe account is not yet verified. Please complete onboarding.',
                    'stripe_account' => $account,
                ]);
            }

            $user = User::where('email', $email)->first();
            if ($user) {
                $user->connected_account_id = $accountId;
                $user->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Stripe account connected and verified successfully.',
                'stripe_account' => $account,
            ]);
        } catch (Exception $e) {
            Log::error('Stripe Connected Account Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function paymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|numeric|exists:events,id',
            'amount' => 'required|numeric',
            'payment_method_types' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // cents
                'currency' => 'usd',
                'payment_method_types' => [$request->payment_method_types], // example: 'card'
                'payment_method' => 'pm_card_visa', // ✅ test card method ID
                'confirmation_method' => 'automatic',
                'confirm' => true,
                'metadata' => [
                    'user_id' => Auth::id(),
                    'event_id' => $request->event_id,
                ],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment intent created successfully.',
                'data' => $paymentIntent,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function paymentSuccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);
            if ($paymentIntent->status === 'succeeded') {  // succeeded or requires_payment_method

                if (Auth::user()->role == 'ORGANIZER') {
                    $event = Event::where('id', $paymentIntent->metadata->event_id)->first();

                    $event->status = 'Upcoming';
                    $event->save();

                    $transaction = Transaction::create([
                        'payment_intent_id' => $paymentIntent->id,
                        'user_id' => Auth::id(),
                        'event_id' => $event->id,
                        'type' => 'Deposit',
                        'amount' => $event->prize_amount,
                        'data' => Carbon::now()->format('Y-m-d'),
                        'status' => 'Completed',
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Event deposite successfully completed.',
                        'data' => $transaction,
                    ], 200);
                } else {
                    $event = Event::where('id', $paymentIntent->metadata->event_id)->first();

                    $transaction = Transaction::create([
                        'payment_intent_id' => $paymentIntent->id,
                        'user_id' => Auth::id(),
                        'event_id' => $event->id,
                        'type' => 'Entry Fee',
                        'amount' => $event->entry_fee,
                        'data' => Carbon::now()->format('Y-m-d'),
                        'status' => 'Completed',
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Entry fee successfully completed.',
                        'data' => $transaction,
                    ], 200);
                }

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment failed. Status: ' . $paymentIntent->status,
                ], 400);
            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
