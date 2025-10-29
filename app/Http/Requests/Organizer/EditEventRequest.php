<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class EditEventRequest extends FormRequest
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
        $rules = [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sport_type' => 'nullable|string|in:single,team',
            'starting_date' => 'nullable|date',
            'ending_date' => 'nullable|date|after_or_equal:starting_date',
            'time' => 'nullable|date_format:h:i A',
            'location' => 'nullable|string|max:255',
            'entry_free' => 'nullable|numeric|min:0',
            'prize_amount' => 'nullable|numeric|min:0',
            'prize_distribution' => 'nullable',
            'rules_guidelines' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:20480',
        ];

        // ✅ Conditional validation based on sport_type
        if ($this->input('sport_type') === 'single') {
            $rules['number_of_player_required'] = 'nullable|integer|min:1';
        } elseif ($this->input('sport_type') === 'team') {
            $rules['number_of_team_required'] = 'nullable|integer|min:1';
            $rules['number_of_player_required_in_a_team'] = 'nullable|integer|min:1';
        }

        return $rules;
    }
}
