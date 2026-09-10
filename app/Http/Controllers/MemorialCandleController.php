<?php

namespace App\Http\Controllers;

use App\Models\PetMemorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemorialCandleController extends Controller
{
    /** Toggle the signed-in user's candle for this memorial. */
    public function store(Request $request, PetMemorial $memorial): RedirectResponse
    {
        $candle = $memorial->candles()->where('user_id', $request->user()->id)->first();

        if ($candle) {
            $candle->delete();
            $message = 'Your candle has been put out.';
        } else {
            $memorial->candles()->create(['user_id' => $request->user()->id]);
            $message = 'You lit a candle. 🕯️';
        }

        return redirect()
            ->route('memorials.show', $memorial)
            ->with('success', $message);
    }
}
