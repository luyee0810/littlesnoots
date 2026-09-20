<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetController extends Controller
{
    /** Public, filterable listing of adoptable pets (Petfinder-style facets). */
    public function index(Request $request): View
    {
        $pets = Pet::query()
            ->published()
            ->with(['species', 'breed', 'photos', 'organization'])
            ->when($request->filled('species'), fn ($q) => $q->whereHas('species', fn ($s) => $s->where('slug', $request->string('species'))))
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('status', $request->string('status')),
                fn ($q) => $q->available()
            )
            ->when($request->filled('age'), fn ($q) => $q->where('age_group', $request->string('age')))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $request->string('gender')))
            ->when($request->filled('size'), fn ($q) => $q->where('size', $request->string('size')))
            ->when($request->filled('coat'), fn ($q) => $q->where('coat', $request->string('coat')))
            // Boolean attribute facets — apply only when the box is ticked.
            ->when($request->boolean('good_with_children'), fn ($q) => $q->where('good_with_children', true))
            ->when($request->boolean('good_with_dogs'), fn ($q) => $q->where('good_with_dogs', true))
            ->when($request->boolean('good_with_cats'), fn ($q) => $q->where('good_with_cats', true))
            ->when($request->boolean('house_trained'), fn ($q) => $q->where('house_trained', true))
            ->when($request->boolean('special_needs'), fn ($q) => $q->where('special_needs', true))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('pets.index', [
            'pets' => $pets,
            'species' => Species::orderBy('name')->get(),
            'filters' => $request->all(),
        ]);
    }

    public function show(Pet $pet): View
    {
        // Drafts and listings awaiting moderation are visible only to their
        // lister and to staff — anyone else gets a 403, even with the slug.
        $this->authorize('view', $pet);

        $pet->load(['species', 'breed', 'secondaryBreed', 'photos', 'organization', 'lister']);

        return view('pets.show', ['pet' => $pet]);
    }
}
