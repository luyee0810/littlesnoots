<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Members — the people behind the listings, bookings and messages.
 *
 * Role changes are admin-only; suspension is open to staff, except against
 * another staff member. The guards live on User::canBeModeratedBy().
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $users = User::query()
            ->withCount(['listedPets', 'adoptionApplications', 'bookings'])
            ->with('providerProfile')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term));
            })
            ->when($filter === 'staff', fn ($q) => $q->whereIn('role', ['staff', 'admin']))
            ->when($filter === 'suspended', fn ($q) => $q->whereNotNull('suspended_at'))
            ->when($filter === 'providers', fn ($q) => $q->whereHas('providerProfile'))
            ->when($filter === 'unverified', fn ($q) => $q->whereNull('email_verified_at'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filter' => $filter,
            'counts' => [
                'all' => User::count(),
                'staff' => User::whereIn('role', ['staff', 'admin'])->count(),
                'providers' => User::whereHas('providerProfile')->count(),
                'suspended' => User::whereNotNull('suspended_at')->count(),
                'unverified' => User::whereNull('email_verified_at')->count(),
            ],
        ]);
    }

    /** One member, and everything of theirs — the view you want during a dispute. */
    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user->load([
                'providerProfile',
                'suspender',
                'listedPets.photos',
                'adoptionApplications.pet',
                'bookings.providerProfile.user',
                'bookings.category',
                'memorials',
            ]),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        // Role changes are the keys to the site — admins only.
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($user->canBeModeratedBy($request->user()), 403);

        $validated = $request->validate([
            'role' => ['required', Rule::in(['adopter', 'staff', 'admin'])],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "{$user->name} is now {$validated['role']}.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->canBeModeratedBy($request->user()), 403);

        $validated = $request->validate([
            // Shown to them at the login screen, so it has to mean something.
            'suspension_reason' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        $user->suspend($request->user(), $validated['suspension_reason']);

        return back()->with('success', "{$user->name} has been suspended and signed out.");
    }

    public function reinstate(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->canBeModeratedBy($request->user()), 403);

        $user->reinstate();

        return back()->with('success', "{$user->name} can sign in again.");
    }
}
