<?php

namespace App\Services\Admin;

use App\Models\Report;

class DisputeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getDisputes()
    {
        return Report::all();

    }

    public function reportSolve($id)
    {
        $report = Report::where('id',$id)->first();
        $report->status = 'Solved';
        $report->save();

        return $report;
    }
}
