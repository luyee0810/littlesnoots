@extends('layouts.app')

@section('title', "Applications for {$pet->name} — Little Snoots")

@section('content')
    <div class="shell-mid page">
        <div class="page-head">
            <div>
                <p class="kicker"><a href="{{ route('listings.index') }}">← My listings</a></p>
                <h1>Applications for {{ $pet->name }}</h1>
                <p class="lede" style="margin-top:.5rem">
                    {{ $applications->count() }} {{ Str::plural('application', $applications->count()) }} ·
                    <span class="badge">{{ ucfirst($pet->status) }}</span>
                </p>
            </div>
            <a href="{{ route('listings.edit', $pet) }}" class="btn btn--outline btn--sm">Edit listing</a>
        </div>

        @include('listings.partials.errors')

        @forelse ($applications as $application)
            <article class="panel" style="margin-top:1.25rem">
                <div class="panel__head">
                    <div>
                        <h2>{{ $application->applicant_name }}</h2>
                        <p class="panel__note">
                            Applied {{ $application->created_at->diffForHumans() }}
                            @if ($application->reviewed_at)
                                · decided by {{ $application->reviewer?->name ?? 'you' }}
                                {{ $application->reviewed_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    <span class="badge badge--{{ match ($application->status) {
                        'pending' => 'submitted',
                        'reviewing' => 'draft',
                        'approved' => 'approved',
                        default => 'rejected',
                    } }}">{{ $application->statusLabel() }}</span>
                </div>

                <div class="panel__body">
                    <dl class="detail-grid">
                        <div>
                            <dt>Email</dt>
                            <dd><a href="mailto:{{ $application->applicant_email }}">{{ $application->applicant_email }}</a></dd>
                        </div>
                        @if ($application->applicant_phone)
                            <div>
                                <dt>Phone</dt>
                                <dd><a href="tel:{{ $application->applicant_phone }}">{{ $application->applicant_phone }}</a></dd>
                            </div>
                        @endif
                        @if ($application->home_type)
                            <div><dt>Home</dt><dd>{{ ucfirst($application->home_type) }}</dd></div>
                        @endif
                        <div><dt>Other pets</dt><dd>{{ $application->has_other_pets ? 'Yes' : 'No' }}</dd></div>
                    </dl>

                    @if ($application->message)
                        <div style="margin-top:1.25rem">
                            <h3>Their message</h3>
                            <p style="margin-top:.5rem;white-space:pre-line">{{ $application->message }}</p>
                        </div>
                    @endif

                    @if ($application->staff_notes)
                        <div style="margin-top:1.25rem">
                            <h3>Your notes</h3>
                            <p style="margin-top:.5rem;white-space:pre-line">{{ $application->staff_notes }}</p>
                        </div>
                    @endif

                    @if ($application->isOpen())
                        <form method="POST" action="{{ route('applications.update', $application) }}"
                              class="form-grid" style="margin-top:1.5rem">
                            @csrf @method('PATCH')

                            <div class="field">
                                <label for="notes-{{ $application->id }}">Notes</label>
                                <textarea name="staff_notes" id="notes-{{ $application->id }}" rows="2" class="textarea"
                                          maxlength="2000"
                                          placeholder="Anything worth remembering. Included in the email if you approve or decline.">{{ $application->staff_notes }}</textarea>
                            </div>

                            <div class="field">
                                <label for="pet-status-{{ $application->id }}">If you approve, mark {{ $pet->name }} as</label>
                                <select name="pet_status" id="pet-status-{{ $application->id }}" class="input">
                                    <option value="">Leave as {{ $pet->status }}</option>
                                    <option value="pending">Adoption pending</option>
                                    <option value="adopted">Adopted</option>
                                </select>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                @if ($application->status === 'pending')
                                    <button type="submit" name="decision" value="reviewing" class="btn btn--ghost btn--sm">
                                        Mark as considering
                                    </button>
                                @endif
                                <button type="submit" name="decision" value="rejected" class="btn btn--outline btn--sm">
                                    Not this time
                                </button>
                                <button type="submit" name="decision" value="approved" class="btn btn--accent btn--sm">
                                    Approve
                                </button>
                            </div>
                            <p class="field-hint">Approving or declining emails the applicant.</p>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty" style="margin-top:2rem">
                <p><strong>No applications yet.</strong></p>
                <p class="field-hint">
                    @if ($pet->isPublished())
                        {{ $pet->name }}’s listing is live — we’ll email you the moment someone applies.
                    @else
                        This listing isn’t live yet, so adopters can’t see it.
                    @endif
                </p>
            </div>
        @endforelse
    </div>
@endsection
