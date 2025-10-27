<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id() ?? null; // current user id

        return [
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:20480', // 20 MB
            'full_name' => 'nullable|string|max:255',
            'user_name' => "nullable|string|max:255|unique:users,user_name,{$userId}",
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];

    }
}
