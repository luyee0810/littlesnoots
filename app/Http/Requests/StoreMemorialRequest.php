<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemorialRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is behind `auth`; any signed-in user may create a memorial.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pet_name' => ['required', 'string', 'max:255'],
            'species' => ['nullable', 'string', 'max:255'],
            'born_on' => ['nullable', 'date', 'before_or_equal:today'],
            'passed_on' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:born_on'],
            'tribute' => ['required', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:4096'],   // 4 MB
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'passed_on.after_or_equal' => 'The date of passing must be on or after the date of birth.',
        ];
    }
}
