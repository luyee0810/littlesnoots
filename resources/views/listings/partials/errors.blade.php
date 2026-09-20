@if ($errors->any())
    <div class="alert alert--bad" style="margin-top:1.5rem">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
