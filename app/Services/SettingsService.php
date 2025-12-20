<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    public function __construct()
    {
        //
    }
    public function editProfile(array $data): array
    {
        $user = Auth::user();

        $userData = collect($data)->only([
            // user data
            'full_name',
            'user_name',
            'phone_number',
            'address',
            'instagram_link',
            'country'
        ])->toArray();

        if (isset($data['avatar'])) {
            if ($user->avatar && Storage::disk('public')->exists(str_replace('/storage/', '', $user->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }
            $path = $data['avatar']->store('avatars', 'public');
            $userData['avatar'] = '/storage/' . $path;
        }

        $profileData = collect($data)->only([
            // profile date
        ])->toArray();

        if (!empty($userData)) {
            $user->update($userData);
        }

        if (!empty($profileData)) {
            $user->profile()->update($profileData);
        }

        return [
            'success' => true,
            'data' => $user->load('profile')
        ];
    }
}
