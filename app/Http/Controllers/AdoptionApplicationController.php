<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdoptionApplicationRequest;
use App\Models\Pet;
use App\Notifications\AdoptionApplicationReceived;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;

class AdoptionApplicationController extends Controller
{
    /** Store an enquiry about a pet, submitted from the pet detail page. */
    public function store(StoreAdoptionApplicationRequest $request, Pet $pet): RedirectResponse
    {
        if ($pet->status !== 'available') {
            return back()->with('error', "Sorry, {$pet->name} is no longer available.");
        }

        $application = $pet->applications()->create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        // Whoever listed the pet handles the enquiry — without this it sits
        // unseen until they next log in.
        Notify::send($pet->lister, new AdoptionApplicationReceived($application));

        return redirect()
            ->route('pets.show', $pet)
            ->with('success', "Thanks for asking about {$pet->name}! Your enquiry has been received and our team will be in touch soon.");
    }
}
