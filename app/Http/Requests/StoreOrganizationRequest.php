<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route is behind the `staff` middleware already.
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(['shelter', 'rescue', 'foster', 'individual'])],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:191'],
            'address1' => ['nullable', 'string', 'max:191'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'mission_statement' => ['nullable', 'string', 'max:2000'],
            'adoption_policy' => ['nullable', 'string', 'max:2000'],
            'facebook' => ['nullable', 'url', 'max:191'],
            'instagram' => ['nullable', 'url', 'max:191'],
            'hours' => ['nullable', 'array'],
            'hours.*' => ['nullable', 'string', 'max:40'],
        ];
    }
}
