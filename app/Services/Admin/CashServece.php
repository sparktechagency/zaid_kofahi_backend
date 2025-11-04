<?php

namespace App\Services\Admin;

use App\Models\Cash;
use Illuminate\Validation\ValidationException;

class CashServece
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function getCashRequests()
    {
        $cashes = Cash::with([
            'player' => function ($q) {
                $q->select('id', 'full_name');
            },
            'team.player' => function ($q) {
                $q->select('id', 'full_name');
            },
            'event' => function ($q) {
                $q->select('id', 'title', 'sport_type');
            },
            'branch'
        ])->latest()->get();

        return $cashes;
    }
    public function cashVerification($id)
    {
        $cash = Cash::where('id', $id)->first();

        if (!$cash) {
            throw ValidationException::withMessages([
                'message' => 'Cash request ID not found.',
            ]);
        }

        $cash->status = 'Verified';
        $cash->save();

        return $cash;
    }

    public function deleteRequest($id)
    {
        $cash = Cash::find($id);

        if (!$cash) {
            throw ValidationException::withMessages([
                'message' => 'Cash request ID not found.',
            ]);
        }

        $cash->delete();

        return true;
    }
}
