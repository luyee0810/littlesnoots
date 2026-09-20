{{-- Where a sitter stands with moderation. Without this an unapproved sitter
     sees an ordinary-looking dashboard and no clue why nobody can book them. --}}
@php($profile = auth()->user()?->providerProfile)

@if ($profile && ! $profile->isLive())
    <div @class([
        'alert',
        'alert--bad' => $profile->status === 'suspended',
        'alert--warn' => $profile->status !== 'suspended',
    ]) style="margin-top:1.5rem">
        @if ($profile->status === 'pending')
            <strong>Your profile is awaiting approval.</strong>
            <p style="margin-top:.35rem">
                Owners can’t find you yet. We check every new sitter — usually within a day.
                Adding your services and rates meanwhile will speed things up.
            </p>
        @elseif ($profile->status === 'suspended')
            <strong>Your profile isn’t listed.</strong>
            @if ($profile->review_notes)
                <p style="margin-top:.35rem">{{ $profile->review_notes }}</p>
            @endif
            <form method="POST" action="{{ route('provider.resubmit') }}" style="margin-top:.75rem">
                @csrf
                <button type="submit" class="btn btn--outline btn--sm">Send for review again</button>
            </form>
        @else
            <strong>Your profile is a draft.</strong>
            <p style="margin-top:.35rem">Finish setting it up to be listed.</p>
        @endif
    </div>
@endif
