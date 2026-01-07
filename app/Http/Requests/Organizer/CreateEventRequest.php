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
            'sport_name' => 'required|string',
            // 'starting_date' => 'required|date',
            // 'ending_date' => 'required|date|after_or_equal:starting_date',

            // starting_date must be AFTER today (today allowed নয়)
            'starting_date' => 'required|date|after:today',
            // ending_date must be AFTER starting_date (equal allowed নয়)
            'ending_date' => 'required|date|after:starting_date',

            'time' => 'required|date_format:g:i A',
            // 'time' => 'required|regex:/^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)$/',
            'location' => 'required|string|max:255',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'entry_fee' => 'required|numeric|min:0',
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
