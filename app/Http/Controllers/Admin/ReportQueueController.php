<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Everything readers have flagged, newest first. */
class ReportQueueController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('status', 'open');

        $reports = Report::query()
            ->with(['reportable', 'reporter', 'reviewer'])
            ->when(
                in_array($filter, ['open', 'actioned', 'dismissed'], true),
                fn ($q) => $q->where('status', $filter),
            )
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'filter' => $filter,
            'counts' => Report::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    /** Take the content down. Closes every open report on the same item. */
    public function remove(Request $request, Report $report): RedirectResponse
    {
        $content = $report->reportable;

        $report->markActioned($request->user());

        // The row may already be gone if someone deleted it in the meantime.
        $content?->delete();

        return back()->with('success', 'Content removed.');
    }

    /** Nothing wrong with it — the content stays. */
    public function dismiss(Request $request, Report $report): RedirectResponse
    {
        $report->markDismissed($request->user());

        return back()->with('success', 'Report dismissed; the content stays up.');
    }
}
