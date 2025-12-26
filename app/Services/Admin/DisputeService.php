<?php

namespace App\Services\Admin;

use App\Models\Report;
use Illuminate\Validation\ValidationException;

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
        $report = Report::where('id', $id)->first();

        if (!$report) {
            throw ValidationException::withMessages([
                'message' => 'Report id not found.',
            ]);
        }

        $report->status = 'Solved';
        $report->save();

        return $report;
    }
}
