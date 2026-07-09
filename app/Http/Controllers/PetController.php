<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetController extends Controller
{
    /** Public, filterable listing of adoptable pets. */
    public function index(Request $request): View
    {
        $pets = Pet::query()
            ->published()
            ->with(['species', 'breed', 'photos'])
            ->when($request->filled('species'), function ($query) use ($request) {
                $query->whereHas('species', fn ($q) => $q->where('slug', $request->string('species')));
            })
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('status', $request->string('status')),
                fn ($q) => $q->available()
            )
            ->when($request->filled('size'), fn ($q) => $q->where('size', $request->string('size')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'ilike', $term)
                    ->orWhere('description', 'ilike', $term));
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('pets.index', [
            'pets' => $pets,
            'species' => Species::orderBy('name')->get(),
            'filters' => $request->only(['species', 'status', 'size', 'q']),
        ]);
    }

    public function show(Pet $pet): View
    {
        $pet->load(['species', 'breed', 'photos', 'lister']);

        return view('pets.show', ['pet' => $pet]);
    }
}
