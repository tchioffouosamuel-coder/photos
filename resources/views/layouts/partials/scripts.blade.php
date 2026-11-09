{{--
  | Scripts communs ACID.
  | Expose window.AcidUI et conserve l'API globale showNotification(message, type)
  | utilisee par les pages existantes.
--}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

<script>
(function () {
    'use strict';

    const ICONS = {
        success: 'fa-circle-check',
        error:   'fa-circle-exclamation',
        warning: 'fa-triangle-exclamation',
        info:    'fa-circle-info',
    };

    /* ---------------------------------------------------------------
     | Notifications (toasts)
     * ------------------------------------------------------------- */
    function notify(message, type = 'success', timeout = 5000) {
        const container = document.getElementById('acidToasts');
        if (!container || !message) return null;

        const kind  = ICONS[type] ? type : 'info';
        const toast = document.createElement('div');
        toast.className = 'acid-toast acid-toast--' + kind;
        toast.setAttribute('role', kind === 'error' ? 'alert' : 'status');
        toast.setAttribute('aria-live', kind === 'error' ? 'assertive' : 'polite');

        const icon = document.createElement('i');
        icon.className = 'fas ' + ICONS[kind] + ' acid-toast__icon';
        icon.setAttribute('aria-hidden', 'true');

        const body = document.createElement('div');
        body.className = 'acid-toast__body';
        body.textContent = message;          /* textContent : pas d'injection HTML */

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'acid-toast__close';
        close.setAttribute('aria-label', 'Fermer la notification');
        close.innerHTML = '<i class="fas fa-xmark" aria-hidden="true"></i>';
        close.addEventListener('click', () => dismiss(toast));

        toast.append(icon, body, close);
        container.appendChild(toast);

        if (timeout > 0) {
            setTimeout(() => dismiss(toast), timeout);
        }
        return toast;
    }

    function dismiss(toast) {
        if (!toast || !toast.parentNode) return;
        toast.classList.add('is-leaving');
        setTimeout(() => toast.remove(), 220);
    }

    /* ---------------------------------------------------------------
     | Navigation mobile
     * ------------------------------------------------------------- */
    function initNav() {
        const toggle  = document.getElementById('acidNavToggle');
        const overlay = document.getElementById('acidOverlay');
        if (!toggle) return;

        const setOpen = (open) => {
            document.body.classList.toggle('acid-nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        toggle.addEventListener('click', () => {
            setOpen(!document.body.classList.contains('acid-nav-open'));
        });

        if (overlay) overlay.addEventListener('click', () => setOpen(false));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') setOpen(false);
        });

        /* Referme apres navigation au clavier/tactile */
        document.querySelectorAll('.acid-sidebar__link').forEach((link) => {
            link.addEventListener('click', () => setOpen(false));
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 991.98) setOpen(false);
        });
    }

    /* ---------------------------------------------------------------
     | Recherche instantanee dans la page
     | Cible tout element portant data-acid-searchable (ligne de tableau,
     | carte, vignette). Affiche data-acid-empty si aucun resultat.
     * ------------------------------------------------------------- */
    function initSearch() {
        const input = document.getElementById('acidSearch');
        if (!input) return;

        const normalize = (value) => (value || '')
            .toString()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .trim();

        const run = () => {
            const needle = normalize(input.value);
            const items  = document.querySelectorAll('[data-acid-searchable]');
            if (!items.length) return;

            let visible = 0;
            items.forEach((item) => {
                const haystack = normalize(
                    item.getAttribute('data-acid-searchable') || item.textContent
                );
                const match = !needle || haystack.includes(needle);
                item.hidden = !match;
                if (match) visible++;
            });

            document.querySelectorAll('[data-acid-search-empty]').forEach((el) => {
                el.hidden = !(needle && visible === 0);
            });
            document.querySelectorAll('[data-acid-search-count]').forEach((el) => {
                el.textContent = visible;
            });
        };

        let timer = null;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(run, 120);
        });

        /* Raccourci : Ctrl/Cmd + K */
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                input.focus();
                input.select();
            }
        });
    }

    /* ---------------------------------------------------------------
     | Confirmation avant action destructive
     | Usage : <form data-acid-confirm="Supprimer cette classe ?">
     * ------------------------------------------------------------- */
    function initConfirm() {
        document.addEventListener('submit', (e) => {
            const form = e.target.closest('[data-acid-confirm]');
            if (!form) return;
            if (!window.confirm(form.getAttribute('data-acid-confirm'))) {
                e.preventDefault();
            }
        });
    }

    /* ---------------------------------------------------------------
     | Etat de chargement des boutons de soumission
     * ------------------------------------------------------------- */
    function initBusyButtons() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-acid-no-busy')) return;
            /* Une confirmation refusee annule la soumission : ne pas bloquer le bouton */
            if (e.defaultPrevented) return;

            form.querySelectorAll('[type="submit"]').forEach((btn) => {
                if (btn.disabled) return;
                const label = btn.innerHTML;
                btn.dataset.acidLabel = label;
                btn.disabled = true;
                btn.innerHTML =
                    '<span class="acid-spinner acid-spinner--sm" aria-hidden="true"></span> Traitement...';
                /* Filet de securite : si la navigation echoue, on restaure */
                setTimeout(() => {
                    if (btn.dataset.acidLabel) {
                        btn.disabled = false;
                        btn.innerHTML = btn.dataset.acidLabel;
                        delete btn.dataset.acidLabel;
                    }
                }, 20000);
            });
        });
    }

    /* ---------------------------------------------------------------
     | Infobulles Bootstrap
     * ------------------------------------------------------------- */
    function initTooltips() {
        if (!window.bootstrap) return;
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            new window.bootstrap.Tooltip(el);
        });
    }

    /* ---------------------------------------------------------------
     | API publique
     * ------------------------------------------------------------- */
    window.AcidUI = { notify, dismiss };

    /* Compatibilite avec le code existant */
    window.showNotification = notify;

    document.addEventListener('DOMContentLoaded', () => {
        initNav();
        initSearch();
        initConfirm();
        initBusyButtons();
        initTooltips();
    });
})();
</script>

{{-- Messages de session --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success'))
            window.AcidUI.notify(@json(session('success')), 'success');
        @endif
        @if (session('error'))
            window.AcidUI.notify(@json(session('error')), 'error', 8000);
        @endif
        @if (session('warning'))
            window.AcidUI.notify(@json(session('warning')), 'warning', 7000);
        @endif
        @if (session('info'))
            window.AcidUI.notify(@json(session('info')), 'info');
        @endif
        @if (session('status'))
            window.AcidUI.notify(@json(session('status')), 'success');
        @endif
    });
</script>

@stack('scripts')
