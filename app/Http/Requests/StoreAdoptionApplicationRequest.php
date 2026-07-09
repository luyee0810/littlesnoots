<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdoptionApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public form — anyone may submit an adoption application.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
            'home_type' => ['nullable', 'in:apartment,house,other'],
            'has_other_pets' => ['sometimes', 'boolean'],
        ];
    }
}
