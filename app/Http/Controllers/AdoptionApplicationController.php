<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdoptionApplicationRequest;
use App\Models\Pet;
use Illuminate\Http\RedirectResponse;

class AdoptionApplicationController extends Controller
{
    /** Store an enquiry about a pet, submitted from the pet detail page. */
    public function store(StoreAdoptionApplicationRequest $request, Pet $pet): RedirectResponse
    {
        if ($pet->status !== 'available') {
            return back()->with('error', "Sorry, {$pet->name} is no longer available.");
        }

        $pet->applications()->create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('pets.show', $pet)
            ->with('success', "Thanks for asking about {$pet->name}! Your enquiry has been received and our team will be in touch soon.");
    }
}
