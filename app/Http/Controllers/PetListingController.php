<?php

namespace App\Http\Controllers;

use App\Actions\StorePetPhoto;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Models\Breed;
use App\Models\Organization;
use App\Models\Pet;
use App\Models\PetPhoto;
use App\Models\Species;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Rehoming a pet, from the lister's side.
 *
 * Anyone signed in may list — a shelter, a rescuer or a one-off fosterer — so
 * nothing here assumes an organisation. Listings are moderated before they go
 * public; see Admin\PetModerationController.
 */
class PetListingController extends Controller
{
    /** The lister's own listings, whatever state they're in. */
    public function index(Request $request): View
    {
        return view('listings.index', [
            'pets' => Pet::listedBy($request->user())
                ->with(['photos', 'species', 'breed'])
                ->withCount('applications')
                ->latest()
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Pet::class);

        return view('listings.create', [
            'pet' => new Pet(['status' => 'available', 'review_status' => 'draft']),
            ...$this->formOptions(),
        ]);
    }

    public function store(StorePetRequest $request, StorePetPhoto $storePhoto): RedirectResponse
    {
        $pet = Pet::create([
            ...$request->safe()->except(['photos']),
            'slug' => $this->uniqueSlug($request->string('name')),
            'listed_by' => $request->user()->id,
            'status_changed_at' => now(),
            'review_status' => 'draft',
        ]);

        foreach ($request->file('photos', []) as $file) {
            $storePhoto->handle($pet, $file);
        }

        return redirect()
            ->route('listings.edit', $pet)
            ->with('success', "{$pet->name}'s listing has been saved. Add photos, then send it for review.");
    }

    public function edit(Pet $pet): View
    {
        $this->authorize('update', $pet);

        return view('listings.edit', [
            'pet' => $pet->load('photos'),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdatePetRequest $request, Pet $pet, StorePetPhoto $storePhoto): RedirectResponse
    {
        $pet->fill($request->safe()->except(['photos']));

        if ($pet->isDirty('status')) {
            $pet->status_changed_at = now();
        }

        $pet->save();

        foreach ($request->file('photos', []) as $file) {
            $storePhoto->handle($pet, $file);
        }

        return redirect()
            ->route('listings.edit', $pet)
            ->with('success', 'Listing updated.');
    }

    /** Hand the listing to a moderator. */
    public function submit(Request $request, Pet $pet): RedirectResponse
    {
        $this->authorize('update', $pet);

        if (! $pet->photos()->exists()) {
            return back()->with('error', 'Add at least one photo before sending this listing for review.');
        }

        $pet->submitForReview();

        return redirect()
            ->route('listings.index')
            ->with('success', "{$pet->name}'s listing has been sent for review. We'll be in touch shortly.");
    }

    public function destroy(Pet $pet): RedirectResponse
    {
        $this->authorize('delete', $pet);

        $pet->delete();   // soft delete — applications and photos are kept

        return redirect()
            ->route('listings.index')
            ->with('success', 'Listing removed.');
    }

    // ---- Photos --------------------------------------------------------

    public function destroyPhoto(Pet $pet, PetPhoto $photo): RedirectResponse
    {
        $this->authorize('update', $pet);
        abort_unless($photo->pet_id === $pet->id, 404);

        // Only delete uploads — seeded photos live in public/images, off the disk.
        if (! str_starts_with($photo->path, '/') && ! str_starts_with($photo->path, 'http')) {
            Storage::disk('public')->delete($photo->path);
        }

        $wasPrimary = $photo->is_primary;
        $photo->delete();

        if ($wasPrimary) {
            $pet->photos()->oldest('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Photo removed.');
    }

    public function makePhotoPrimary(Pet $pet, PetPhoto $photo): RedirectResponse
    {
        $this->authorize('update', $pet);
        abort_unless($photo->pet_id === $pet->id, 404);

        $pet->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        return back()->with('success', 'Main photo updated.');
    }

    // ---- Helpers -------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'species' => Species::orderBy('name')->get(),
            'breeds' => Breed::orderBy('name')->get(['id', 'name', 'species_id']),
            // Only staff may attach a shelter. Letting anyone pick any
            // organisation lets a stranger borrow a real rescue's reputation
            // and publish its phone number on a listing it knows nothing about.
            'organizations' => request()->user()?->isStaff()
                ? Organization::orderBy('name')->get(['id', 'name'])
                : collect(),
        ];
    }

    /** Slugs are the route key, so two pets called Luna can't collide. */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'pet';
        $slug = $base;

        for ($i = 2; Pet::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
