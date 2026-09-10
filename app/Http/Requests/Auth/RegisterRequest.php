<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:50'],
            // How the member describes themselves at sign-up:
            //   adopter  — a pet owner adopting, booking services, or posting a memorial
            //   shelter  — a shelter, rescue, or individual rehoming pets (gets staff tools)
            //   provider — offers pet services; goes on to the provider onboarding form
            'account_type' => ['required', 'in:adopter,shelter,provider'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Map the public account type to an internal user role. Providers are ordinary
     * users — being a provider is an approved profile, not a role — so they land on
     * `adopter` and are pushed through onboarding after registering.
     */
    public function role(): string
    {
        return $this->validated('account_type') === 'shelter' ? 'staff' : 'adopter';
    }

    public function wantsToProvideServices(): bool
    {
        return $this->validated('account_type') === 'provider';
    }
}
