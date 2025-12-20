<?php

namespace App\Services\Admin;

use App\Models\Branch;

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
        return Branch::findOrFail($id);
    }

    public function editBranch($id, array $data)
    {
        $branch = Branch::findOrFail($id);
        
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
        $branch = Branch::findOrFail($id);
        $branch->delete();
        return true;
    }
}
