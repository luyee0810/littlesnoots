@extends('layouts.app')

@section('title', 'Services & rates — Two Fat Cats')

@section('content')
    <div class="shell-mid page">
        @include('provider.partials.nav', ['active' => 'services'])

        <div class="page-head" style="margin-top:2rem">
            <div>
                <h1>Services &amp; rates</h1>
                <p class="lede" style="margin-top:.5rem">What you offer, and what you charge for it.</p>
            </div>
            <a href="{{ route('provider.services.create') }}" class="btn btn--accent">
                <i data-lucide="plus" aria-hidden="true"></i> Add a service
            </a>
        </div>

        @if ($services->isEmpty())
            <div class="empty" style="margin-top:2rem">
                <p class="empty__icon">🐾</p>
                <h2>No services listed yet</h2>
                <p>Owners can’t book you until you add at least one service.</p>
                <a href="{{ route('provider.services.create') }}" class="btn btn--accent btn--sm">Add your first service</a>
            </div>
        @else
            <div class="panel rows" style="margin-top:2rem">
                @foreach ($services as $service)
                    <div class="row" style="align-items:flex-start">
                        <div style="min-width:0">
                            <h2 style="font-size:1.05rem">
                                <i class="svc-icon" data-lucide="{{ $service->category->lucideIcon() }}" aria-hidden="true"></i> {{ $service->label() }}
                                @unless ($service->is_active)
                                    <span class="chip" style="margin-left:.35rem">Hidden</span>
                                @endunless
                            </h2>
                            @if ($service->description)
                                <p class="meta" style="margin-top:.35rem">{{ $service->description }}</p>
                            @endif
                            <p class="meta" style="margin-top:.35rem;font-size:.78rem">
                                Up to {{ $service->max_pets }} {{ Str::plural('pet', $service->max_pets) }}
                                @if ($service->additional_pet_price)
                                    · +RM {{ number_format($service->additional_pet_price, 0) }} per extra pet
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-5">
                            <div style="text-align:right">
                                <div class="price">RM {{ number_format($service->price, 0) }}</div>
                                <div class="label">{{ $service->category->priceSuffix() }}</div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('provider.services.edit', $service) }}" class="btn btn--outline btn--xs">Edit</a>
                                <form method="POST" action="{{ route('provider.services.destroy', $service) }}"
                                      onsubmit="return confirm('Remove this service?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--outline btn--danger btn--xs">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
