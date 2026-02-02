// gestion upload formulaire adhesion depuis page profil

document.addEventListener('DOMContentLoaded', function() {
    const btnDeposer = document.getElementById('btn-deposer-adhesion');
    const btnSoumettre = document.getElementById('btn-soumettre-adhesion');
    const fileInput = document.getElementById('formulaire-adhesion');
    const fileStatus = document.getElementById('file-status');
    const formulaire = document.getElementById('form-adhesion');

    if (!btnDeposer || !fileInput || !fileStatus || !formulaire || !btnSoumettre) {
        return;
    }

    let fichierSelectionne = false;

    // clic sur bouton deposer ouvre selecteur fichier
    btnDeposer.addEventListener('click', function() {
        fileInput.click();
    });

    // detecter selection fichier
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            // verifier taille (5 Mo max)
            if (file.size > 5 * 1024 * 1024) {
                fileStatus.textContent = '❌ Fichier trop volumineux (5 Mo maximum)';
                fileStatus.className = 'file-status error';
                fileInput.value = '';
                fichierSelectionne = false;
                btnDeposer.textContent = 'Déposer mon formulaire';
                btnSoumettre.disabled = true;
                return;
            }

            // verifier extension
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['pdf', 'jpg', 'jpeg'].includes(ext)) {
                fileStatus.textContent = '❌ Format non autorisé (PDF, JPG ou JPEG uniquement)';
                fileStatus.className = 'file-status error';
                fileInput.value = '';
                fichierSelectionne = false;
                btnDeposer.textContent = 'Déposer mon formulaire';
                btnSoumettre.disabled = true;
                return;
            }

            // fichier valide
            fileStatus.textContent = `✓ Fichier sélectionné : ${file.name} (${(file.size / 1024).toFixed(2)} Ko)`;
            fileStatus.className = 'file-status success';
            fichierSelectionne = true;

            // changer texte bouton deposer
            btnDeposer.textContent = 'Remplacer mon formulaire';

            // activer bouton soumettre
            btnSoumettre.disabled = false;
        } else {
            // aucun fichier
            fileStatus.textContent = '';
            fileStatus.className = 'file-status';
            fichierSelectionne = false;
            btnDeposer.textContent = 'Déposer mon formulaire';
            btnSoumettre.disabled = true;
        }
    });

    // clic sur bouton soumettre
    btnSoumettre.addEventListener('click', function() {
        if (fichierSelectionne) {
            formulaire.submit();
        } else {
            fileStatus.textContent = '❌ Veuillez d\'abord sélectionner un fichier';
            fileStatus.className = 'file-status error';
        }
    });
});
