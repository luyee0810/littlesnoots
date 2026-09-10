<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemorialMessageRequest;
use App\Models\PetMemorial;
use Illuminate\Http\RedirectResponse;

class MemorialMessageController extends Controller
{
    public function store(StoreMemorialMessageRequest $request, PetMemorial $memorial): RedirectResponse
    {
        $memorial->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()
            ->route('memorials.show', $memorial)
            ->with('success', 'Your message has been added.')
            ->withFragment('guestbook');
    }
}
