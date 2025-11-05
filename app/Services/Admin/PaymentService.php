<?php

namespace App\Services\Admin;

use App\Models\Payment;

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
        return Payment::latest()->paginate();
    }

    public function confirmPayment($id)
    {
        return 'confirm Payment';
    }
}
