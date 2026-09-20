<?php

namespace App\Http\Requests;

class UpdatePetRequest extends StorePetRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('pet')) ?? false;
    }
}
