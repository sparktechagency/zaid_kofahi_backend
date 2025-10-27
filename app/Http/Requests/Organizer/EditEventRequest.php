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
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sport_type' => 'nullable|string',
            'entry_type' => 'nullable|string',
            'starting_date' => 'nullable|date',
            'ending_date' => 'nullable|date',
            'time' => 'nullable|date_format:h:i A',
            'location' => 'nullable|string|max:255',
            'number_of_player_required' => 'nullable|integer|min:0',
            'entry_free' => 'nullable|numeric|min:0',
            'prize_amount' => 'nullable|numeric|min:0',
            'prize_distribution' => 'nullable',
            'rules_guidelines' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:20480',
        ];
    }
}
