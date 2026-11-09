@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'acid-error']) }} role="alert">
        <i class="fas fa-circle-exclamation" aria-hidden="true" style="margin-top:.15rem;"></i>
        <span>
            @foreach ((array) $messages as $message)
                <span style="display:block;">{{ $message }}</span>
            @endforeach
        </span>
    </div>
@endif
