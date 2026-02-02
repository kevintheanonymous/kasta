// gestion upload formulaire adhesion et popup avertissement

document.addEventListener('DOMContentLoaded', function() {
    const btnDeposer = document.getElementById('btn-deposer-adhesion');
    const fileInput = document.getElementById('formulaire-adhesion');
    const fileStatus = document.getElementById('file-status');
    const formulaire = document.querySelector('form');
    const submitBtn = document.getElementById('submit');

    let fichierAdhesionValide = false;

    // clic sur bouton deposer ouvre selecteur fichier
    if (btnDeposer) {
        btnDeposer.addEventListener('click', function() {
            fileInput.click();
        });
    }

    // detecter selection fichier
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                // verifier taille (5 Mo max)
                if (file.size > 5 * 1024 * 1024) {
                    fileStatus.textContent = '❌ Fichier trop volumineux (5 Mo maximum)';
                    fileStatus.className = 'file-status error';
                    fileInput.value = '';
                    fichierAdhesionValide = false;
                    return;
                }

                // verifier extension
                const ext = file.name.split('.').pop().toLowerCase();
                if (!['pdf', 'jpg', 'jpeg'].includes(ext)) {
                    fileStatus.textContent = '❌ Format non autorisé (PDF, JPG ou JPEG uniquement)';
                    fileStatus.className = 'file-status error';
                    fileInput.value = '';
                    fichierAdhesionValide = false;
                    return;
                }

                // fichier valide
                fileStatus.textContent = `✓ Fichier sélectionné : ${file.name} (${(file.size / 1024).toFixed(2)} Ko)`;
                fileStatus.className = 'file-status success';
                fichierAdhesionValide = true;
            } else {
                fileStatus.textContent = '';
                fileStatus.className = 'file-status';
                fichierAdhesionValide = false;
            }
        });
    }

    // intercepter soumission formulaire pour popup avertissement
    if (formulaire && submitBtn) {
        formulaire.addEventListener('submit', function(e) {
            // si pas de fichier adhesion valide, afficher popup
            if (!fichierAdhesionValide && (!fileInput || !fileInput.files.length)) {
                // verifier si popup deja confirmee via data attribute
                if (!formulaire.hasAttribute('data-popup-confirmee')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    afficherPopupAvertissement();
                    return false;
                }
            }
            // Sinon laisser le formulaire se soumettre
            return true;
        });
    }

    // fonction afficher popup avertissement
    function afficherPopupAvertissement() {
        // creer overlay
        const overlay = document.createElement('div');
        overlay.id = 'popup-overlay';

        // creer popup
        const popup = document.createElement('div');
        popup.id = 'popup-adhesion';

        popup.innerHTML = `
            <h2>⚠️ Avertissement</h2>
            <p>
                Vous vous apprêtez à vous inscrire en tant que <strong>NON adhérent</strong>.<br><br>
                Vous ne serez <strong>PAS couvert par l'assurance du club</strong> en cas d'accident
                lors des événements organisés par l'association.
            </p>
            <p class="popup-warning">
                Souhaitez-vous continuer ?
            </p>
            <div class="popup-buttons">
                <button id="popup-non" class="popup-btn popup-btn-non">Non, revenir en arrière</button>
                <button id="popup-oui" class="popup-btn popup-btn-oui">Oui, continuer</button>
            </div>
        `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // bouton NON - fermer popup
        document.getElementById('popup-non').addEventListener('click', function() {
            document.body.removeChild(overlay);
            formulaire.removeAttribute('data-popup-confirmee');
            // reactiver le bouton submit
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-loading', 'btn-disabled');
            }
        });

        // bouton OUI - soumettre formulaire
        document.getElementById('popup-oui').addEventListener('click', function() {
            document.body.removeChild(overlay);
            // marquer formulaire comme confirme
            formulaire.setAttribute('data-popup-confirmee', 'true');
            // creer un input submit temporaire et le cliquer pour soumettre
            const tempSubmit = document.createElement('input');
            tempSubmit.type = 'submit';
            tempSubmit.style.display = 'none';
            formulaire.appendChild(tempSubmit);
            tempSubmit.click();
            formulaire.removeChild(tempSubmit);
        });
    }
});
