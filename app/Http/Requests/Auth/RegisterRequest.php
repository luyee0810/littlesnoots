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
            // "lister" = someone posting pets for adoption; "adopter" = a potential adopter.
            'account_type' => ['required', 'in:adopter,lister'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /** Map the public account type to an internal user role. */
    public function role(): string
    {
        return $this->validated('account_type') === 'lister' ? 'staff' : 'adopter';
    }
}
