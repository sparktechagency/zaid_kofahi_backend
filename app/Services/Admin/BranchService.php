<?php

namespace App\Services\Admin;

use App\Models\Branch;
use Illuminate\Validation\ValidationException;

class BranchService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getBranches()
    {
        return Branch::all();
    }
    public function createBranch(array $data)
    {
        return Branch::create($data);
    }
    public function viewBranch($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            throw ValidationException::withMessages([
                'payment' => 'Branch id not found.',
            ]);
        }

        return $branch;
    }
    public function editBranch($id, array $data)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            throw ValidationException::withMessages([
                'message' => 'Branch id not found.',
            ]);
        }

        $branch->name = $data['name'] ?? $branch->name;
        $branch->location = $data['location'] ?? $branch->location;
        $branch->latitude = $data['latitude'] ?? $branch->latitude;
        $branch->longitude = $data['longitude'] ?? $branch->longitude;
        $branch->country = $data['country'] ?? $branch->country;
        $branch->working_hour = $data['working_hour'] ?? $branch->working_hour;
        $branch->save();

        return $branch;
    }
    public function deleteBranch($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            throw ValidationException::withMessages([
                'message' => 'Branch id not found.',
            ]);
        }

        $branch->delete();

        return true;
    }
}
