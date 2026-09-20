@extends('layouts.app')

@section('title', 'Reported content — Admin')

@section('content')
    <div class="shell-wide page">
        @include('admin.partials.nav')

        <div class="page-head" style="margin-top:1.5rem">
            <div>
                <p class="kicker">Back of house</p>
                <h1>Reported content</h1>
                <p class="lede" style="margin-top:.5rem">
                    Flagged by readers. Nothing here is hidden until you remove it.
                </p>
            </div>
        </div>

        <div class="filter-bar" style="margin-top:1.25rem">
            @foreach (['open' => 'Open', 'actioned' => 'Removed', 'dismissed' => 'Dismissed', 'all' => 'All'] as $key => $label)
                <a href="{{ route('admin.reports.index', ['status' => $key]) }}"
                   @class(['chip', 'is-on' => $filter === $key])>
                    {{ $label }}
                    @if (($counts[$key] ?? 0))
                        <span class="chip__count">{{ $counts[$key] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="stack" style="margin-top:1.5rem">
            @forelse ($reports as $report)
                <article class="panel">
                    <div class="panel__head">
                        <div>
                            <h2>{{ $report->reasonLabel() }}</h2>
                            <p class="panel__note">
                                {{ class_basename($report->reportable_type) === 'Review' ? 'Sitter review' : 'Memorial message' }}
                                · reported by {{ $report->reporter?->name ?? 'a deleted account' }}
                                {{ $report->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="badge badge--{{ match ($report->status) {
                            'open' => 'submitted',
                            'actioned' => 'rejected',
                            default => 'approved',
                        } }}">{{ ucfirst($report->status) }}</span>
                    </div>

                    <div class="panel__body">
                        @if ($report->reportable)
                            <blockquote class="reported-quote">{{ $report->reportable->reportSummary() }}</blockquote>
                            <p class="field-hint" style="margin-top:.5rem">
                                Written by {{ $report->reportable->user?->name ?? 'a deleted account' }} ·
                                <a href="{{ $report->reportable->reportUrl() }}">See it in context</a>
                            </p>
                        @else
                            <p class="field-hint">This content has already been deleted.</p>
                        @endif

                        @if ($report->notes)
                            <div style="margin-top:1rem">
                                <h3>What the reporter said</h3>
                                <p style="margin-top:.35rem;white-space:pre-line">{{ $report->notes }}</p>
                            </div>
                        @endif

                        @if ($report->status === 'open')
                            <div class="flex flex-wrap items-center gap-3" style="margin-top:1.25rem">
                                <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn--outline btn--sm">Leave it up</button>
                                </form>

                                @if ($report->reportable)
                                    <form method="POST" action="{{ route('admin.reports.remove', $report) }}"
                                          onsubmit="return confirm('Remove this content permanently?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn--accent btn--sm">Remove content</button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <p class="field-hint" style="margin-top:1rem">
                                {{ $report->status === 'actioned' ? 'Removed' : 'Dismissed' }}
                                by {{ $report->reviewer?->name }} {{ $report->reviewed_at?->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty">
                    <p><strong>Nothing reported.</strong></p>
                    <p class="field-hint">No reports match this filter.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top:1.5rem">{{ $reports->links() }}</div>
    </div>
@endsection
