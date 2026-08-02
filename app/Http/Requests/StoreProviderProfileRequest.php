<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $profileId = $this->user()?->providerProfile?->id;

        return [
            'headline' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],

            'address1' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'service_radius_km' => ['required', 'integer', 'min:1', 'max:100'],

            'years_experience' => ['required', 'integer', 'min:0', 'max:60'],
            'home_type' => ['nullable', 'string', 'max:50'],
            'has_fenced_yard' => ['sometimes', 'boolean'],
            'has_own_pets' => ['sometimes', 'boolean'],
            'is_smoke_free' => ['sometimes', 'boolean'],
            'has_insurance' => ['sometimes', 'boolean'],

            'accepts_species' => ['nullable', 'array'],
            'accepts_species.*' => ['string', Rule::exists('species', 'slug')],
            'accepts_sizes' => ['nullable', 'array'],
            'accepts_sizes.*' => ['in:small,medium,large,xlarge'],
            'max_pets_per_booking' => ['required', 'integer', 'min:1', 'max:10'],

            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],

            // Only present when editing an existing profile.
            'slug' => [
                'sometimes', 'string', 'max:255', 'alpha_dash',
                Rule::unique('provider_profiles', 'slug')->ignore($profileId),
            ],
        ];
    }
}
