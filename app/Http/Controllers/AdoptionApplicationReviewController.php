<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Notifications\AdoptionApplicationDecided;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Working through the people who applied for a pet.
 *
 * Lives on the lister's side (/rehome), not in /admin: whoever listed the pet
 * handles their own applicants, and staff can do the same for any pet.
 */
class AdoptionApplicationReviewController extends Controller
{
    /** Everyone who applied for one pet. */
    public function index(Request $request, Pet $pet): View
    {
        $this->authorize('viewApplications', $pet);

        return view('listings.applications', [
            'pet' => $pet->load('photos'),
            'applications' => $pet->applications()
                ->with(['user', 'reviewer'])
                ->orderByRaw("case when status = 'pending' then 0 when status = 'reviewing' then 1 else 2 end")
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Move an application along. One endpoint rather than three, because the
     * form is one row of buttons and the guard lives in the model either way.
     */
    public function update(Request $request, AdoptionApplication $application): RedirectResponse
    {
        $this->authorize('review', $application);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['reviewing', 'approved', 'rejected'])],
            'staff_notes' => ['nullable', 'string', 'max:2000'],
            // Approving usually means the pet is spoken for — offer it, don't assume it.
            'pet_status' => ['nullable', Rule::in(['pending', 'adopted'])],
        ]);

        if (! $application->canTransitionTo($validated['decision'])) {
            return back()->with('error', 'That application has already been decided.');
        }

        $user = $request->user();
        $notes = $validated['staff_notes'] ?? null;

        match ($validated['decision']) {
            'reviewing' => $application->markReviewing($user, $notes),
            'approved' => $application->markApproved($user, $notes),
            'rejected' => $application->markRejected($user, $notes),
        };

        if ($validated['decision'] === 'approved' && ! empty($validated['pet_status'])) {
            $pet = $application->pet;
            $pet->update([
                'status' => $validated['pet_status'],
                'status_changed_at' => now(),
            ]);
        }

        // The applicant hears about a decision, not about being picked up.
        if (in_array($validated['decision'], ['approved', 'rejected'], true)) {
            $this->notifyApplicant($application);
        }

        return back()->with('success', $this->confirmation($application));
    }

    /** The applicant pulling out, from their own dashboard. */
    public function withdraw(Request $request, AdoptionApplication $application): RedirectResponse
    {
        $this->authorize('withdraw', $application);

        $application->markWithdrawn();

        return back()->with('success', 'Your application has been withdrawn.');
    }

    private function notifyApplicant(AdoptionApplication $application): void
    {
        $application->user
            ? Notify::send($application->user, new AdoptionApplicationDecided($application))
            : Notify::toEmail($application->applicant_email, new AdoptionApplicationDecided($application));
    }

    private function confirmation(AdoptionApplication $application): string
    {
        return match ($application->status) {
            'reviewing' => "{$application->applicant_name}'s application is marked as being considered.",
            'approved' => "{$application->applicant_name}'s application approved — they've been emailed.",
            'rejected' => "{$application->applicant_name} has been let down gently.",
            default => 'Application updated.',
        };
    }
}
