<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'sport_type' => 'required|string|in:single,team',
            'starting_date' => 'required|date',
            'ending_date' => 'required|date|after_or_equal:starting_date',
            'time' => 'required|date_format:h:i A',
            'location' => 'required|string|max:255',
            'entry_free' => 'required|numeric|min:0',
            'prize_amount' => 'required|numeric|min:0',
            'prize_distribution' => 'required',
            'rules_guidelines' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:20480',
        ];

        // ✅ Conditional validation based on sport_type
        if ($this->input('sport_type') === 'single') {
            $rules['number_of_player_required'] = 'required|integer|min:1';
        } elseif ($this->input('sport_type') === 'team') {
            $rules['number_of_team_required'] = 'required|integer|min:1';
            $rules['number_of_player_required_in_a_team'] = 'required|integer|min:1';
        }

        return $rules;
    }
}
