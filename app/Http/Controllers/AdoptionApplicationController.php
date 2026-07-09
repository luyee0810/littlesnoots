<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdoptionApplicationRequest;
use App\Models\Pet;
use Illuminate\Http\RedirectResponse;

class AdoptionApplicationController extends Controller
{
    /** Store an adoption application submitted from a pet detail page. */
    public function store(StoreAdoptionApplicationRequest $request, Pet $pet): RedirectResponse
    {
        if ($pet->status !== 'available') {
            return back()->with('error', "Sorry, {$pet->name} is no longer available for adoption.");
        }

        $pet->applications()->create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('pets.show', $pet)
            ->with('success', "Thank you! Your application for {$pet->name} has been received. Our team will be in touch soon.");
    }
}
