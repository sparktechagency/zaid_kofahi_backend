<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class SelectedWinnerRequest extends FormRequest
{
    public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        'winners' => 'required|array|min:1',
        'winners.*.place' => 'required|string',
        'winners.*.player_id' => 'required|integer|exists:users,id',
        'winners.*.team_id' => 'nullable|integer|exists:teams,id',
        'winners.*.amount' => 'required|numeric|min:0',
        'winners.*.additional_prize' => 'nullable|string|max:255',
    ];
}

public function messages()
{
    return [
        'winners.required' => 'Winners data is required.',
        'winners.array' => 'Winners must be an array.',
        'winners.*.player_id.required' => 'Player ID is required for each winner.',
        'winners.*.amount.required' => 'Amount is required for each winner.',
    ];
}

}
