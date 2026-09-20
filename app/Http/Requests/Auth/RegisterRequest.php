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
            // How the member describes themselves at sign-up. This picks where we
            // send them next — it grants nothing. Listing pets is open to every
            // member, and staff access is granted by an admin, never self-selected.
            'account_type' => ['required', 'in:adopter,shelter,provider'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Everyone registers as an adopter.
     *
     * "Shelter" used to map to `staff`, which now means the /admin area —
     * moderation queues, member records and suspension. Anyone could have
     * ticked a radio button on the public sign-up form and walked in. Rehoming
     * needs no role at all (see PetPolicy::create), and staff is granted by an
     * admin from /admin/members.
     */
    public function role(): string
    {
        return 'adopter';
    }

    public function wantsToRehome(): bool
    {
        return $this->validated('account_type') === 'shelter';
    }

    public function wantsToProvideServices(): bool
    {
        return $this->validated('account_type') === 'provider';
    }
}
