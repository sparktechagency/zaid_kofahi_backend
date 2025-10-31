<?php

namespace App\Services\Admin;

use App\Models\User;

class UserService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getUsers()
    {
        $query = User::query();
        $query->whereIn('role',['PLAYER','ORGANIZER']);
        return $query->get();
    }
}
