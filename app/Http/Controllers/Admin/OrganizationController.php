<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Shelters and rescues — reference records a pet listing can point at.
 *
 * Staff-managed rather than self-serve: attaching a shelter to a listing
 * borrows its reputation and publishes its phone number, so the records
 * themselves stay under staff control.
 */
class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.organizations.index', [
            'organizations' => Organization::query()
                ->withCount('pets')
                ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.organizations.form', ['organization' => new Organization(['country' => 'MY'])]);
    }

    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $organization = Organization::create([
            ...$request->validated(),
            'slug' => $this->uniqueSlug($request->string('name')),
            'hours' => $this->hours($request),
        ]);

        return redirect()
            ->route('admin.organizations.edit', $organization)
            ->with('success', "{$organization->name} has been added.");
    }

    public function edit(Organization $organization): View
    {
        return view('admin.organizations.form', ['organization' => $organization]);
    }

    public function update(StoreOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $organization->update([
            ...$request->validated(),
            'hours' => $this->hours($request),
        ]);

        return back()->with('success', 'Shelter updated.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        // Pets keep their listing; organization_id is nullable and nullOnDelete.
        $organization->delete();

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Shelter removed. Its listings are untouched.');
    }

    /**
     * @return array<string, string>|null
     */
    private function hours(Request $request): ?array
    {
        $hours = array_filter($request->input('hours', []), fn ($value) => filled($value));

        return $hours ?: null;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'shelter';
        $slug = $base;

        for ($i = 2; Organization::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
