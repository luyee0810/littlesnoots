{{-- Delete / report controls for a piece of user-written content.
     Expects $content (a Reportable model), $type (the ReportController key),
     and optionally $deletable (bool). --}}
@php($deletable = $deletable ?? false)
@php($user = auth()->user())

<div class="content-actions">
    @if ($deletable)
        <form method="POST" action="{{ $deleteRoute }}"
              onsubmit="return confirm('Remove this permanently?')">
            @csrf @method('DELETE')
            <button type="submit" class="content-actions__btn">Delete</button>
        </form>
    @endif

    @auth
        @if ($content->user_id !== $user->id)
            @if ($content->isReportedBy($user))
                <span class="content-actions__done" title="A moderator will take a look">Reported</span>
            @else
                <details class="content-actions__report">
                    <summary class="content-actions__btn">Report</summary>
                    <form method="POST" action="{{ route('reports.store') }}" class="report-form">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="id" value="{{ $content->id }}">

                        <label for="reason-{{ $type }}-{{ $content->id }}">What’s wrong with it?</label>
                        <select name="reason" id="reason-{{ $type }}-{{ $content->id }}" class="input input--sm" required>
                            @foreach (\App\Models\Report::REASONS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <label for="notes-{{ $type }}-{{ $content->id }}" class="sr-only">Anything to add?</label>
                        <textarea name="notes" id="notes-{{ $type }}-{{ $content->id }}" rows="2" maxlength="1000"
                                  class="textarea" placeholder="Anything to add? (optional)"></textarea>

                        <button type="submit" class="btn btn--outline btn--sm">Send report</button>
                    </form>
                </details>
            @endif
        @endif
    @endauth
</div>
