@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'acid-auth-status']) }} role="status">
        <i class="fas fa-circle-check" aria-hidden="true" style="margin-top:.15rem;"></i>
        <span>{{ $status }}</span>
    </div>
@endif
