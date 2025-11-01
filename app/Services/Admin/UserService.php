<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Validation\ValidationException;

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
        $query->whereIn('role', ['PLAYER', 'ORGANIZER']);
        return $query->get();
    }
    public function viewUser($id)
    {
        return User::findOrFail($id);
    }
    public function blockUnblockToggle($id)
    {
        $user = User::where('id', $id)->whereIn('role',['PLAYER','ORGANIZER'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'message' => 'User not found.',
            ]);
        }

        $user->status = $user->status == 'Active' ? 'Suspended' : 'Active';
        $user->save();

        return $user;
    }
}
