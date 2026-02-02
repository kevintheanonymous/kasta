// Helper pour forcer la saisie en format français (jj/mm/aaaa, HH:mm)
(function () {
    const SELECTOR_DATE = '.js-date-fr';
    const SELECTOR_TIME = '.js-time-fr';
    const SELECTOR_DATETIME = '.js-datetime-fr';

    function pad(n) {
        return n.toString().padStart(2, '0');
    }

    function toFrDate(iso) {
        if (!iso) return '';
        const parts = iso.split('T')[0].split('-');
        if (parts.length !== 3) return '';
        const [y, m, d] = parts;
        return `${pad(d)}-${pad(m)}-${y}`.replace(/-/g, '/');
    }

    const ISO_DATE = /^(\d{4})-(\d{2})-(\d{2})$/;
    const ISO_TIME = /^(\d{2}):(\d{2})$/;
    const ISO_DATETIME = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})$/;

    function toISODateFromFr(fr) {
        if (!fr) return '';
        if (ISO_DATE.test(fr)) return fr; // déjà ISO
        const m = fr.match(/^(\d{2})[\/-](\d{2})[\/-](\d{4})$/);
        if (!m) return '';
        const [, d, mth, y] = m;
        return `${y}-${mth}-${d}`;
    }

    function toISOTimeFromFr(fr) {
        if (!fr) return '';
        if (ISO_TIME.test(fr)) return fr; // déjà ISO
        const m = fr.match(/^(\d{2}):(\d{2})$/);
        if (!m) return '';
        const [, h, min] = m;
        return `${h}:${min}`;
    }

    function toISODatetimeFromFr(fr) {
        if (!fr) return '';
        if (ISO_DATETIME.test(fr)) return fr; // déjà ISO
        const m = fr.match(/^(\d{2})[\/-](\d{2})[\/-](\d{4})[ T](\d{2}):(\d{2})$/);
        if (!m) return '';
        const [, d, mth, y, h, min] = m;
        return `${y}-${mth}-${d}T${h}:${min}`;
    }

    function parseDateFr(fr) {
        const iso = toISODateFromFr(fr);
        if (!iso) return null;
        const d = new Date(iso + 'T00:00:00');
        return isNaN(d.getTime()) ? null : d;
    }

    function parseDatetimeFr(fr) {
        const iso = toISODatetimeFromFr(fr);
        if (!iso) return null;
        const d = new Date(iso);
        return isNaN(d.getTime()) ? null : d;
    }

    function normalizeInput(el) {
        if (el.tagName !== 'INPUT') return;
        const original = el.value;

        if (window.flatpickr) {
            // Bascule en text pour éviter le native picker, flatpickr fournira l'UI FR.
            el.type = 'text';
            // Ne pas modifier la valeur, flatpickr va la gérer avec defaultDate
            return;
        }

        if (el.classList.contains('js-date-fr')) {
            el.placeholder = 'jj/mm/aaaa';
            if (original && original.includes('-')) {
                el.value = toFrDate(original) || original;
            }
        }

        if (el.classList.contains('js-time-fr')) {
            el.placeholder = 'hh:mm';
            if (original && ISO_TIME.test(original)) {
                el.value = original;
            }
        }

        if (el.classList.contains('js-datetime-fr')) {
            el.placeholder = 'jj/mm/aaaa hh:mm';
            if (original && ISO_DATETIME.test(original)) {
                const [datePart, timePart] = original.split(/[ T]/);
                const frDate = toFrDate(datePart) || datePart;
                const time = timePart ? timePart.slice(0, 5) : '';
                el.value = time ? `${frDate} ${time}` : frDate;
            }
        }
    }

    function convertForSubmit(el) {
        if (el.type !== 'text') {
            // On ne touche pas aux inputs natifs (date/time/datetime-local).
            el.setCustomValidity('');
            return true;
        }

        if (el.type !== 'text' && window.flatpickr) {
            // flatpickr remplit déjà la valeur en ISO sur l'input source.
            el.setCustomValidity('');
            return true;
        }

        if (el.classList.contains('js-date-fr')) {
            const iso = toISODateFromFr(el.value.trim());
            if (!iso) {
                el.setCustomValidity('Format attendu : jj/mm/aaaa');
                return false;
            }
            el.value = iso;
            el.setCustomValidity('');
            return true;
        }
        if (el.classList.contains('js-time-fr')) {
            const iso = toISOTimeFromFr(el.value.trim());
            if (!iso) {
                el.setCustomValidity('Format attendu : hh:mm');
                return false;
            }
            el.value = iso;
            el.setCustomValidity('');
            return true;
        }
        if (el.classList.contains('js-datetime-fr')) {
            const iso = toISODatetimeFromFr(el.value.trim());
            if (!iso) {
                el.setCustomValidity('Format attendu : jj/mm/aaaa hh:mm');
                return false;
            }
            el.value = iso;
            el.setCustomValidity('');
            return true;
        }
        return true;
    }

    function initFlatpickr(inputs) {
        if (!window.flatpickr || !window.flatpickr.l10ns || !window.flatpickr.l10ns.fr) return;

        inputs.forEach(el => {
            const opts = { locale: 'fr' };
            
            // Récupérer la valeur originale (peut être en FR ou ISO)
            let originalValue = el.value;
            
            if (el.classList.contains('js-date-fr')) {
                // Convertir en ISO si nécessaire pour flatpickr
                const isoValue = toISODateFromFr(originalValue) || originalValue;
                
                Object.assign(opts, {
                    altInput: true,
                    altFormat: 'd/m/Y',
                    dateFormat: 'Y-m-d',
                    defaultDate: isoValue || null
                });
            }
            if (el.classList.contains('js-time-fr')) {
                Object.assign(opts, {
                    enableTime: true,
                    noCalendar: true,
                    time_24hr: true,
                    altInput: true,
                    altFormat: 'H:i',
                    dateFormat: 'H:i',
                    defaultDate: originalValue || null
                });
            }
            if (el.classList.contains('js-datetime-fr')) {
                // Convertir en ISO si nécessaire pour flatpickr
                // Remplacer le T par un espace pour correspondre au dateFormat de flatpickr
                let isoValue = toISODatetimeFromFr(originalValue) || originalValue;
                if (isoValue && isoValue.includes('T')) {
                    isoValue = isoValue.replace('T', ' ');
                }
                
                Object.assign(opts, {
                    enableTime: true,
                    time_24hr: true,
                    altInput: true,
                    altFormat: 'd/m/Y H:i',
                    dateFormat: 'Y-m-d H:i',
                    defaultDate: isoValue || null
                });
            }
            flatpickr(el, opts);
        });
    }

    function init() {
        const inputs = document.querySelectorAll(`${SELECTOR_DATE}, ${SELECTOR_TIME}, ${SELECTOR_DATETIME}`);
        inputs.forEach(normalizeInput);

        if (window.flatpickr) {
            initFlatpickr(inputs);
        }

        const forms = new Set(Array.from(inputs).map(el => el.form).filter(Boolean));
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                let ok = true;
                const scopedInputs = form.querySelectorAll(`${SELECTOR_DATE}, ${SELECTOR_TIME}, ${SELECTOR_DATETIME}`);
                scopedInputs.forEach(el => {
                    if (!convertForSubmit(el)) {
                        ok = false;
                    }
                });
                if (!ok) {
                    e.preventDefault();
                }
            });
        });
    }

    // Expose helpers for other scripts
    window.toISODateFromFr = toISODateFromFr;
    window.toISOTimeFromFr = toISOTimeFromFr;
    window.toISODatetimeFromFr = toISODatetimeFromFr;
    window.parseDateFr = parseDateFr;
    window.parseDatetimeFr = parseDatetimeFr;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
