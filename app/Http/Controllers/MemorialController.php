<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemorialRequest;
use App\Models\PetMemorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemorialController extends Controller
{
    /** Public garden of remembrance — every memorial, newest first. */
    public function index(): View
    {
        $memorials = PetMemorial::query()
            ->with('user')
            ->withCount(['candles', 'messages'])
            ->latest()
            ->paginate(12);

        return view('memorials.index', ['memorials' => $memorials]);
    }

    public function create(): View
    {
        return view('memorials.create', ['memorial' => new PetMemorial]);
    }

    public function store(StoreMemorialRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('memorials', 'public');
        }

        $memorial = PetMemorial::create($data);

        return redirect()
            ->route('memorials.show', $memorial)
            ->with('success', "{$memorial->pet_name}'s memorial has been created. 🕯️");
    }

    public function show(PetMemorial $memorial): View
    {
        $memorial->load(['user', 'candles', 'messages.user']);

        return view('memorials.show', [
            'memorial' => $memorial,
            'lit' => $memorial->isCandledBy(request()->user()),
        ]);
    }

    public function destroy(Request $request, PetMemorial $memorial): RedirectResponse
    {
        abort_unless($memorial->user_id === $request->user()->id, 403);

        // Only clean up uploaded files — bundled/demo paths live outside the disk.
        if ($memorial->photo_path && ! str_starts_with($memorial->photo_path, '/')
            && ! str_starts_with($memorial->photo_path, 'http')) {
            Storage::disk('public')->delete($memorial->photo_path);
        }

        $memorial->delete();

        return redirect()
            ->route('memorials.index')
            ->with('success', 'The memorial has been removed.');
    }
}
