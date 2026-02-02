document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('creneaux-container');
    const addBtn = document.getElementById('add-slot');
    const form = document.getElementById('event-with-slots-form');
    const dateClotureInput = document.querySelector('input[name="date_cloture"]');

    const slotTypes = [
        { value: 'preparation', label: 'Préparation' },
        { value: 'jour_j', label: 'Jour J' },
        { value: 'rangement', label: 'Rangement' }
    ];

    function buildSlotRow(index) {
        const row = document.createElement('div');
        row.className = 'card slot-row';
        row.dataset.index = index;

        // Recuperer les postes disponibles depuis window
        const postes = window.postesDisponibles || [];
        const postesCheckboxes = postes.map(p =>
            `<label class="checkbox-item">
                <input type="checkbox" name="creneaux[${index}][postes][]" value="${p.Id_Poste}" class="poste-checkbox">
                ${p.libelle} (Niveau ${p.niveau})
            </label>`
        ).join('');

        row.innerHTML = `
            <div class="form-row">
                <div class="form-group half">
                    <label>Type</label>
                    <select name="creneaux[${index}][type]" required>
                        ${slotTypes.map(t => `<option value="${t.value}">${t.label}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group half">
                    <label>Date</label>
                    <input type="date" name="creneaux[${index}][date_creneau]" required lang="fr" class="js-date-fr">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group half">
                    <label>Heure de début</label>
                    <input type="time" name="creneaux[${index}][heure_debut]" required lang="fr" class="js-time-fr">
                </div>
                <div class="form-group half">
                    <label>Heure de fin</label>
                    <input type="time" name="creneaux[${index}][heure_fin]" required lang="fr" class="js-time-fr">
                </div>
            </div>
            <div class="form-group">
                <label>Postes disponibles pour ce créneau</label>
                <div class="checkbox-group">
                    <label class="checkbox-item select-all-postes">
                        <input type="checkbox" class="select-all-checkbox">
                        <strong>Tout cocher / décocher</strong>
                    </label>
                    ${postesCheckboxes}
                </div>
                <small class="form-text">Cochez les postes à rendre disponibles pour ce créneau</small>
            </div>
            <div class="form-group">
                <label>Commentaire (optionnel)</label>
                <textarea name="creneaux[${index}][commentaire]" rows="2"></textarea>
            </div>
            <div class="slot-actions">
                <button type="button" class="btn btn-danger btn-sm remove-slot">Supprimer ce créneau</button>
            </div>
        `;

        row.querySelector('.remove-slot').addEventListener('click', () => {
            row.remove();
            renumeroter();
        });

        // Gestion de la case "Tout cocher"
        const selectAllCheckbox = row.querySelector('.select-all-checkbox');
        const posteCheckboxes = row.querySelectorAll('.poste-checkbox');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                posteCheckboxes.forEach(cb => {
                    cb.checked = selectAllCheckbox.checked;
                });
            });
            
            // Mettre à jour la case "Tout cocher" si toutes les cases individuelles sont cochées/décochées
            posteCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const allChecked = Array.from(posteCheckboxes).every(c => c.checked);
                    const noneChecked = Array.from(posteCheckboxes).every(c => !c.checked);
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
                });
            });
        }

        return row;
    }

    function renumeroter() {
        const rows = Array.from(container.querySelectorAll('.slot-row'));
        rows.forEach((row, idx) => {
            row.dataset.index = idx;
            row.querySelectorAll('select, input, textarea').forEach(el => {
                el.name = el.name.replace(/creneaux\[[0-9]+\]/, `creneaux[${idx}]`);
            });
        });
    }

    function addSlot() {
        const index = container.querySelectorAll('.slot-row').length;
        const row = buildSlotRow(index);
        container.appendChild(row);
        
        // Réinitialiser flatpickr sur les nouveaux champs
        const dateInput = row.querySelector('.js-date-fr');
        const timeInputs = row.querySelectorAll('.js-time-fr');
        
        // Convertir les inputs en type text pour éviter le picker natif (anglais)
        if (dateInput) {
            dateInput.type = 'text';
            dateInput.placeholder = 'jj/mm/aaaa';
        }
        timeInputs.forEach(timeInput => {
            timeInput.type = 'text';
            timeInput.placeholder = 'hh:mm';
        });
        
        if (window.flatpickr && window.flatpickr.l10ns && window.flatpickr.l10ns.fr) {
            if (dateInput) {
                flatpickr(dateInput, {
                    locale: 'fr',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    dateFormat: 'Y-m-d'
                });
            }
            
            timeInputs.forEach(timeInput => {
                flatpickr(timeInput, {
                    locale: 'fr',
                    enableTime: true,
                    noCalendar: true,
                    time_24hr: true,
                    altInput: true,
                    altFormat: 'H:i',
                    dateFormat: 'H:i'
                });
            });
        }
    }

    function getClotureDate() {
        if (!dateClotureInput || !dateClotureInput.value) return null;
        return new Date(dateClotureInput.value + 'T00:00:00');
    }

    // Fonction pour afficher une erreur stylisée au lieu d'un alert()
    function showError(message) {
        // Supprimer les anciennes alertes d'erreur
        const oldAlerts = document.querySelectorAll('.alert-danger.js-error');
        oldAlerts.forEach(a => a.remove());
        
        // Créer la nouvelle alerte
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger js-error';
        alertDiv.innerHTML = `<ul><li>${message}</li></ul>`;
        
        // Ajouter le bouton de fermeture
        const closeBtn = document.createElement('button');
        closeBtn.className = 'alert-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Fermer');
        closeBtn.addEventListener('click', () => {
            alertDiv.classList.add('fade-out');
            setTimeout(() => alertDiv.remove(), 300);
        });
        alertDiv.appendChild(closeBtn);
        
        // Insérer l'alerte avant le formulaire ou après le h1
        const h1 = document.querySelector('h1');
        const formContainer = form?.closest('.container') || document.querySelector('.container');
        if (h1 && h1.nextSibling) {
            h1.parentNode.insertBefore(alertDiv, h1.nextSibling);
        } else if (formContainer && form) {
            formContainer.insertBefore(alertDiv, form);
        } else if (form) {
            form.parentNode.insertBefore(alertDiv, form);
        }
        
        // Scroll vers l'alerte
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function validateSlots() {
        const rows = Array.from(container.querySelectorAll('.slot-row'));
        if (rows.length === 0) {
            showError('Ajoutez au moins un créneau.');
            return false;
        }

        const cloture = getClotureDate();

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const dateVal = row.querySelector('input[name*="[date_creneau]"]').value;
            const hStart = row.querySelector('input[name*="[heure_debut]"]').value;
            const hEnd = row.querySelector('input[name*="[heure_fin]"]').value;

            if (!dateVal || !hStart || !hEnd) {
                showError(`Créneau ${i + 1} : tous les champs sont requis.`);
                return false;
            }

            const isoDate = window.toISODateFromFr ? (window.toISODateFromFr(dateVal) || dateVal) : dateVal;
            const isoStart = window.toISOTimeFromFr ? (window.toISOTimeFromFr(hStart) || hStart) : hStart;
            const isoEnd = window.toISOTimeFromFr ? (window.toISOTimeFromFr(hEnd) || hEnd) : hEnd;
            const start = new Date(isoDate + 'T' + isoStart);
            const end = new Date(isoDate + 'T' + isoEnd);

            if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                showError(`Créneau ${i + 1} : date ou heure invalide.`);
                return false;
            }

            if (start >= end) {
                showError(`Créneau ${i + 1} : l'heure de début doit être strictement inférieure à l'heure de fin.`);
                return false;
            }

            if (cloture && start < cloture) {
                showError(`Créneau ${i + 1} : doit commencer après la date de clôture des inscriptions.`);
                return false;
            }
        }

        return true;
    }

    if (addBtn) {
        addBtn.addEventListener('click', addSlot);
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            if (!validateSlots()) {
                e.preventDefault();
            }
        });
    }

    // Initialise avec un créneau par défaut
    addSlot();
});
