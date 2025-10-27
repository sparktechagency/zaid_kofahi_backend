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
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'sport_type' => 'required|string',
            'entry_type' => 'required|string',
            'starting_date' => 'required|date',
            'ending_date' => 'required|date',
            'time' => 'required|date_format:h:i A',
            'location' => 'required|string|max:255',
            'number_of_player_required' => 'required|integer|min:0',
            'entry_free' => 'required|numeric|min:0',
            'prize_amount' => 'required|numeric|min:0',
            'prize_distribution' => 'required',
            'rules_guidelines' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:20480',
        ];
    }
}
