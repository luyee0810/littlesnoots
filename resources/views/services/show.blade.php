@extends('layouts.app')

@section('title', ($category?->name ?? 'Pet services').' — Two Fat Cats')

@section('content')
    <div class="shell page">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('services.index') }}">Pet care</a>
            @if ($category)
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $category->name }}</span>
            @endif
        </nav>

        <div class="page-head" style="margin-top:1rem">
            <div>
                <p class="kicker">{{ $category ? 'Service' : 'Results' }}</p>
                <h1>
                    @if ($category)
                        <span aria-hidden="true">{{ $category->icon }}</span> {{ $category->name }}
                    @else
                        Search results
                    @endif
                </h1>
                @if ($category?->description)
                    <p class="lede" style="margin-top:.75rem">{{ $category->description }}</p>
                @endif
            </div>
        </div>

        <div style="margin-top:2rem">
            @include('partials.service-search', ['categories' => $categories, 'filters' => $filters])
        </div>

        <p class="meta" style="margin-top:1.5rem">
            {{ $providers->total() }} {{ Str::plural('sitter', $providers->total()) }} found
            @if ($filters['location'] !== '')
                near <strong>{{ $filters['location'] }}</strong>
            @endif
            @if ($filters['q'] !== '')
                matching <strong>“{{ $filters['q'] }}”</strong>
            @endif
        </p>

        @if ($providers->isEmpty())
            <div class="empty" style="margin-top:1.5rem">
                <p class="empty__icon">🔍</p>
                <h2>No sitters matched that search</h2>
                <p>Try a broader location, or clear the keywords.</p>
                <a href="{{ route('services.index') }}" class="btn btn--outline btn--sm">Browse all services</a>
            </div>
        @else
            <div class="card-grid" style="margin-top:1.5rem">
                @foreach ($providers as $provider)
                    @include('partials.provider-card', ['provider' => $provider])
                @endforeach
            </div>

            <div class="pagination-wrap">{{ $providers->links() }}</div>
        @endif
    </div>
@endsection
