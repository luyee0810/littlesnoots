{{-- One booking, as it appears on the member dashboard. Expects $booking. --}}
<div class="row">
    <div style="min-width:0">
        <a href="{{ route('bookings.show', $booking) }}" class="link-draw" style="font-weight:600">
            <i class="svc-icon" data-lucide="{{ $booking->category->lucideIcon() }}" aria-hidden="true"></i>
            {{ $booking->category->name }} for {{ $booking->pet_name }}
        </a>
        <p class="meta" style="margin-top:.3rem">
            {{ $booking->providerProfile->user->name }} · {{ $booking->dateRangeLabel() }}
        </p>
    </div>
    <div class="flex items-center gap-4">
        @if ($booking->unread_count ?? 0)
            <a href="{{ route('bookings.show', $booking) }}#messages" class="unread-dot"
               title="{{ $booking->unread_count }} unread {{ Str::plural('message', $booking->unread_count) }}">
                {{ $booking->unread_count }}
            </a>
        @endif
        <span class="price" style="font-size:1rem">{{ $booking->totalLabel() }}</span>
        <span class="status {{ $booking->statusClasses() }}">{{ $booking->statusLabel() }}</span>
    </div>
</div>
