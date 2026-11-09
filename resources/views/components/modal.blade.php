@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

@php
    /*
     | Modale ACID batie sur Bootstrap 5 (le bundle JS est deja charge par le
     | layout). Compatible avec l'API historique : un evenement window
     | "open-modal" / "close-modal" portant le nom de la modale en detail
     | l'ouvre ou la ferme.
     |
     | Ouverture declarative recommandee :
     |   <button data-bs-toggle="modal" data-bs-target="#modal-nom">
     */
    $sizeClass = [
        'sm'  => 'modal-sm',
        'md'  => '',
        'lg'  => 'modal-lg',
        'xl'  => 'modal-lg',
        '2xl' => 'modal-lg',
    ][$maxWidth] ?? 'modal-lg';

    $id = 'modal-' . $name;
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true"
     data-acid-modal="{{ $name }}"
     aria-labelledby="{{ $id }}-label">
    <div class="modal-dialog modal-dialog-centered {{ $sizeClass }}">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            /* Pont evenementiel : open-modal / close-modal (API historique) */
            (function () {
                const find = (name) => document.querySelector('[data-acid-modal="' + name + '"]');

                window.addEventListener('open-modal', function (e) {
                    const el = find(e.detail);
                    if (el && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(el).show();
                });

                window.addEventListener('close-modal', function (e) {
                    const el = find(e.detail);
                    if (el && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(el).hide();
                });
            })();
        </script>
    @endpush
@endonce

@if ($show)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById(@json($id));
                if (el && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(el).show();
            });
        </script>
    @endpush
@endif
