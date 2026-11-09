<button {{ $attributes->merge(['type' => 'submit', 'class' => 'acid-btn acid-btn--primary']) }}>
    {{ $slot }}
</button>
