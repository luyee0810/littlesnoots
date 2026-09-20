<?php

namespace App\Http\Requests;

use App\Models\Pet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Pet::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // ---- Identity ----
            'name' => ['required', 'string', 'max:120'],
            'species_id' => ['required', 'exists:species,id'],
            // Optional by design: rescuers and fosterers rehome without a shelter.
            // Staff-only: see prepareForValidation, which strips it from everyone else.
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'location' => ['nullable', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],

            // ---- Breed ----
            'breed_id' => ['nullable', 'exists:breeds,id'],
            'secondary_breed_id' => ['nullable', 'different:breed_id', 'exists:breeds,id'],
            'breed_mixed' => ['boolean'],
            'breed_unknown' => ['boolean'],

            // ---- Characteristics ----
            'age_group' => ['required', Rule::in(['baby', 'young', 'adult', 'senior'])],
            'age_months' => ['nullable', 'integer', 'min:0', 'max:360'],
            'gender' => ['required', Rule::in(['male', 'female', 'unknown'])],
            'size' => ['required', Rule::in(['small', 'medium', 'large', 'xlarge'])],
            'coat' => ['nullable', Rule::in(['hairless', 'short', 'medium', 'long', 'wire', 'curly'])],
            'color' => ['nullable', 'string', 'max:60'],
            'secondary_color' => ['nullable', 'string', 'max:60'],

            // ---- Status & fee ----
            'status' => ['required', Rule::in(['available', 'pending', 'adopted', 'found', 'unavailable'])],
            'adoption_fee' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],

            // ---- Attributes ----
            'spayed_neutered' => ['boolean'],
            'shots_current' => ['boolean'],
            'house_trained' => ['boolean'],
            'declawed' => ['boolean'],
            'special_needs' => ['boolean'],

            // ---- Environment ----
            'good_with_children' => ['nullable', 'boolean'],
            'good_with_dogs' => ['nullable', 'boolean'],
            'good_with_cats' => ['nullable', 'boolean'],

            // ---- Tags & photos ----
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:30'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],   // 8 MB each
        ];
    }

    /**
     * Unchecked checkboxes never reach the request, so they have to be filled in
     * explicitly or Eloquent keeps the previous value on update.
     */
    protected function prepareForValidation(): void
    {
        // A listing can't claim to come from a shelter unless staff say so.
        if (! $this->user()?->isStaff()) {
            $this->request->remove('organization_id');
        }

        $flags = [
            'breed_mixed', 'breed_unknown', 'spayed_neutered', 'shots_current',
            'house_trained', 'declawed', 'special_needs',
        ];

        $this->merge(array_map(
            fn ($flag) => $this->boolean($flag),
            array_combine($flags, $flags),
        ));

        // The "good with" flags are yes / no / unknown, so null is meaningful.
        foreach (['good_with_children', 'good_with_dogs', 'good_with_cats'] as $field) {
            $value = $this->input($field);
            $this->merge([$field => $value === '' || $value === null ? null : (bool) $value]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Tell adopters about this pet — even a couple of sentences helps.',
            'secondary_breed_id.different' => 'The second breed should differ from the first.',
        ];
    }
}
