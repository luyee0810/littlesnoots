<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasProviderProfile() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $profileId = $this->user()->providerProfile->id;
        $serviceId = $this->route('service')?->id;

        return [
            // One service per category per provider.
            'service_category_id' => [
                'required', 'integer', 'exists:service_categories,id',
                Rule::unique('provider_services', 'service_category_id')
                    ->where('provider_profile_id', $profileId)
                    ->ignore($serviceId),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:1', 'max:99999'],
            'additional_pet_price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'min_units' => ['required', 'integer', 'min:1', 'max:365'],
            'max_pets' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_category_id.unique' => 'You already offer that service — edit the existing one instead.',
        ];
    }
}
