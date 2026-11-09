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
        /* Deux declencheurs pour le meme tiroir : hamburger de la topbar et
           entree "Menu" de la navigation basse. */
        const toggles = [
            document.getElementById('acidNavToggle'),
            document.getElementById('acidNavToggleBottom'),
        ].filter(Boolean);

        const overlay = document.getElementById('acidOverlay');
        if (!toggles.length) return;

        const setOpen = (open) => {
            document.body.classList.toggle('acid-nav-open', open);
            toggles.forEach((t) => t.setAttribute('aria-expanded', open ? 'true' : 'false'));
        };

        toggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                setOpen(!document.body.classList.contains('acid-nav-open'));
            });
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
     | Modales : rattachement au <body>
     |
     | Bootstrap ajoute le fond (.modal-backdrop) directement sous <body>,
     | alors que la modale reste la ou elle est ecrite dans la page. Si un
     | ancetre de la modale forme un contexte d'empilement (transform, filter,
     | opacity, position + z-index...), le z-index de la modale est enferme
     | dans ce contexte et la modale passe SOUS le fond : elle reste visible au
     | travers, mais tous les clics atteignent le fond.
     |
     | Deplacer chaque modale a la racine du <body> -- ce que recommande la
     | documentation de Bootstrap -- supprime la dependance au CSS environnant.
     | Les ecouteurs deja poses survivent au deplacement, et aucun script de la
     | base ne depend des ancetres d'une modale.
     * ------------------------------------------------------------- */
    function initModalHoisting() {
        document.querySelectorAll('.modal').forEach((modal) => {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });
    }

    /* ---------------------------------------------------------------
     | Feuilles du bas : glisser vers le bas pour fermer
     |
     | Sur mobile, acid.css ancre toutes les modales en bas de l'ecran.
     | On y ajoute le geste attendu : un glissement vers le bas depuis
     | l'en-tete (ou la poignee) referme la feuille.
     |
     | Le geste n'est actif que sous 768px et uniquement si le glissement
     | part du haut de la feuille, afin de ne jamais voler le defilement du
     | contenu ni gener une saisie de formulaire.
     * ------------------------------------------------------------- */
    function initSheets() {
        if (!window.bootstrap) return;

        const SEUIL_FERMETURE = 110;   /* px parcourus avant fermeture */
        const ZONE_PRISE = 72;         /* px depuis le haut de la feuille */

        let dialog = null;
        let depart = 0;
        let delta = 0;
        let actif = false;

        const estMobile = () => window.matchMedia('(max-width: 767.98px)').matches;

        document.addEventListener('touchstart', (e) => {
            if (!estMobile() || e.touches.length !== 1) return;

            const candidat = e.target.closest('.modal.show .modal-dialog:not(.modal-dialog--plain)');
            if (!candidat) return;

            const contenu = candidat.querySelector('.modal-content');
            if (!contenu) return;

            /* Uniquement si le doigt se pose dans la zone de prehension */
            const haut = contenu.getBoundingClientRect().top;
            if (e.touches[0].clientY - haut > ZONE_PRISE) return;

            dialog = candidat;
            depart = e.touches[0].clientY;
            delta = 0;
            actif = true;
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (!actif || !dialog) return;

            delta = e.touches[0].clientY - depart;

            /* Glissement vers le haut : on ne fait rien */
            if (delta <= 0) {
                dialog.style.transform = '';
                return;
            }

            dialog.classList.add('is-dragging');
            dialog.style.transform = 'translateY(' + delta + 'px)';
        }, { passive: true });

        const terminer = () => {
            if (!actif || !dialog) return;

            const cible = dialog;
            const parcours = delta;

            cible.classList.remove('is-dragging');
            cible.style.transform = '';

            actif = false;
            dialog = null;
            delta = 0;

            if (parcours > SEUIL_FERMETURE) {
                const modal = cible.closest('.modal');
                if (modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modal).hide();
                }
            }
        };

        document.addEventListener('touchend', terminer, { passive: true });
        document.addEventListener('touchcancel', terminer, { passive: true });
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

    /*
     | Rattachement immediat, et non au DOMContentLoaded : ce partial est
     | inclus apres le contenu de la page, donc toutes les modales sont deja
     | dans le DOM, mais les scripts propres aux pages (@push('scripts')) ne se
     | sont pas encore executes. Certains ouvrent une modale des le chargement
     | (retour d'une validation en echec) : elle doit deja etre a sa place.
     */
    initModalHoisting();

    document.addEventListener('DOMContentLoaded', () => {
        initNav();
        initModalHoisting();   /* idempotent : couvre les modales ajoutees ensuite */
        initSheets();
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
