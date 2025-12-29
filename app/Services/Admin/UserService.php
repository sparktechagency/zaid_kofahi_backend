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
    public function getUsers(?string $search , ?string $filter)
    {
        $query = User::query();

        if (!empty($filter)) {
            $query->where('role', $filter);
        } else {
            $query->whereIn('role', ['PLAYER', 'ORGANIZER']);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->get();
    }
    public function viewUser($id)
    {
        return User::findOrFail($id);
    }
    public function blockUnblockToggle($id)
    {
        $user = User::where('id', $id)->whereIn('role', ['PLAYER', 'ORGANIZER'])->first();

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
