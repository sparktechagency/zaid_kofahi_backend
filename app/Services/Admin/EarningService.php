<?php

namespace App\Services\Admin;

use App\Models\Earning;

class EarningService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function earningList()
    {
        return Earning::latest()->paginate();
    }
}
