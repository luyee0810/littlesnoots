@extends('layouts.app')

@section('title', 'Sitter profile — Little Snoots')

@section('content')
    <div class="shell-mid page">
        @include('provider.partials.nav', ['active' => 'profile'])

        <div class="page-head" style="margin-top:2rem">
            <div>
                <h1>Your sitter profile</h1>
                <p class="lede" style="margin-top:.5rem">This is what pet owners see before they book you.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert--bad" style="margin-top:1.5rem">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('provider.profile.update') }}" class="form-grid" style="margin-top:2rem">
            @csrf
            @method('PUT')

            @include('provider.partials.profile-fields', ['profile' => $profile, 'species' => $species])

            <div class="flex justify-end">
                <button type="submit" class="btn btn--accent">Save changes</button>
            </div>
        </form>
    </div>
@endsection
