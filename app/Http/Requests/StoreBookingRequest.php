<?php

namespace App\Http\Requests;

use App\Models\ProviderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is behind `auth`; anyone signed in may request a booking.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'provider_service_id' => ['required', 'integer', 'exists:provider_services,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],

            'pet_name' => ['required', 'string', 'max:255'],
            'pet_species_id' => ['nullable', 'integer', 'exists:species,id'],
            'pet_breed' => ['nullable', 'string', 'max:255'],
            'pet_size' => ['nullable', 'in:small,medium,large,xlarge'],
            'pet_count' => ['required', 'integer', 'min:1', 'max:10'],
            'pet_notes' => ['nullable', 'string', 'max:2000'],

            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'service_address' => ['nullable', 'string', 'max:255'],
            'service_city' => ['nullable', 'string', 'max:255'],

            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Cross-field checks that need the provider and the service row: the service must
     * belong to this provider, be active, fit the pet count, and the dates must be ones
     * the provider actually works.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $service = $this->serviceOrNull();
                $provider = $this->route('provider');

                if (! $service || ! $provider) {
                    return;
                }

                if ($service->provider_profile_id !== $provider->id || ! $service->is_active) {
                    $validator->errors()->add('provider_service_id', 'That service is not offered by this provider.');

                    return;
                }

                if ($this->integer('pet_count') > $service->max_pets) {
                    $validator->errors()->add('pet_count', "This provider takes at most {$service->max_pets} pet(s) per booking.");
                }

                if ($service->category->requires_date_range && ! $this->filled('ends_at')) {
                    $validator->errors()->add('ends_at', 'Please choose an end date.');
                }

                if ($this->date('starts_at') && ! $provider->isAvailableOn($this->date('starts_at'))) {
                    $validator->errors()->add('starts_at', 'The provider is not available on that date.');
                }
            },
        ];
    }

    /** The requested service, or null when the id is missing/invalid. */
    public function serviceOrNull(): ?ProviderService
    {
        if (! $this->filled('provider_service_id')) {
            return null;
        }

        return ProviderService::with('category')->find($this->input('provider_service_id'));
    }

    public function messages(): array
    {
        return [
            'starts_at.after' => 'Please choose a date in the future.',
            'ends_at.after' => 'The end date must be after the start date.',
        ];
    }
}
