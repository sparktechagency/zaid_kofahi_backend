<?php

namespace App\Services\Admin;

use App\Models\Refund;

class RefundService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function refundList()
    {
        return Refund::latest()->paginate();
    }

    public function confirmRefund($id)
    {
        return 'confirm Refund';
    }

    public function cancelRefund($id)
    {
        return 'cancel Refund';
    }
}
