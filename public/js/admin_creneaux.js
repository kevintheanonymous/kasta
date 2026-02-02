document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const dateInput = document.getElementById('date_creneau');
    const heureInput = document.getElementById('heure_debut');
    const heureFinInput = document.getElementById('heure_fin');
    
    // On récupère la date de clôture depuis un attribut data sur le formulaire ou un input hidden
    // Pour faire propre, on va modifier le PHP pour mettre cette info dans un data-attribute du form
    const formElement = document.querySelector('form.admin-form');
    const dateClotureStr = formElement.dataset.dateCloture;

    // Fonction pour afficher une erreur stylisée au lieu d'un alert()
    function showError(message) {
        // Supprime les anciennes alertes
        const oldAlerts = document.querySelectorAll('.alert-danger.js-error');
        oldAlerts.forEach(a => a.remove());
        
        // Crée l'élément d'alerte
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger js-error';
        alertDiv.innerHTML = `
            <button type="button" class="alert-close" aria-label="Fermer">×</button>
            ${message}
        `;
        
        // Insère l'alerte au début du formulaire
        const container = document.querySelector('.admin-form') || document.querySelector('form');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Bouton de fermeture
            alertDiv.querySelector('.alert-close').addEventListener('click', function() {
                alertDiv.remove();
            });
            
            // Scroll vers l'erreur
            alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    if (form && dateClotureStr) {
        form.addEventListener('submit', function(e) {
            const dateVal = dateInput.value;
            const heureVal = heureInput.value;
            const heureFinVal = heureFinInput ? heureFinInput.value : '';
            
            if(dateVal && heureVal) {
                const isoDate = window.toISODateFromFr ? (window.toISODateFromFr(dateVal) || dateVal) : dateVal;
                const isoDebut = window.toISOTimeFromFr ? (window.toISOTimeFromFr(heureVal) || heureVal) : heureVal;
                const isoFin = heureFinVal ? (window.toISOTimeFromFr ? (window.toISOTimeFromFr(heureFinVal) || heureFinVal) : heureFinVal) : null;
                const creneauDebut = new Date(isoDate + 'T' + isoDebut);
                const creneauFin = isoFin ? new Date(isoDate + 'T' + isoFin) : null;
                const dateCloture = new Date(dateClotureStr.replace(' ', 'T')); // Compatibilité Safari/Firefox

                if (creneauDebut < dateCloture) {
                    e.preventDefault();
                    showError("Le créneau doit commencer après la date de clôture des inscriptions (" + dateClotureStr + ").");
                    return;
                }

                if (creneauFin && creneauDebut >= creneauFin) {
                    e.preventDefault();
                    showError("L'heure de début doit être strictement inférieure à l'heure de fin.");
                    return;
                }
            }
        });
    }
});
