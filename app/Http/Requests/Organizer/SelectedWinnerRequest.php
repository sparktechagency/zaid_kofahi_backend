<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class SelectedWinnerRequest extends FormRequest
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
        return [
            'event_id' => 'required|exists:events,id',
            'player_id' => 'required|exists:users,id',
            'place' => 'required|string|in:1st,2nd,3rd',
            'amount' => 'required|numeric|min:0',
        ];
    }
}
