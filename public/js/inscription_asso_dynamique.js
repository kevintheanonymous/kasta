// Gestion du formulaire d'inscription dynamique aux événements associatifs
// avec calcul de tarif basé sur la participation sportive des 12 derniers mois

(function() {
    'use strict';

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
        const container = document.getElementById('inscription-form') || document.querySelector('.inscription-container');
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

    let accompagnateursCount = 0;
    const accompagnateursData = {}; // stocke les infos de chaque accompagnateur
    let membreTarifCalcule = false;
    let membreTarif = 0;

    const form = document.getElementById('inscription-form');
    const btnAjouter = document.getElementById('btn-ajouter-accompagnateur');
    const container = document.getElementById('accompagnateurs-container');
    const btnSubmit = document.getElementById('btn-submit');
    const montantTotal = document.getElementById('montant-total');
    const nbInvitesHidden = document.getElementById('nb_invites_hidden');
    const accompagnateursDataHidden = document.getElementById('accompagnateurs_data');

    // calcul du tarif pour une personne (via AJAX)
    function calculerTarif(email, index, isMembrePrincipal = false) {
        const tarifDisplay = document.querySelector(`.tarif-display[data-index="${index}"]`);
        const btn = document.querySelector(`.btn-calculer[data-index="${index}"]`);
        
        // Pour le membre principal, on utilise un affichage différent
        if (isMembrePrincipal) {
            const loadingEl = tarifDisplay.querySelector('.tarif-loading');
            const resultEl = tarifDisplay.querySelector('.tarif-result');
            const montantEl = tarifDisplay.querySelector('.tarif-montant');
            const raisonEl = tarifDisplay.querySelector('.tarif-raison');
            
            // recupere le tarif de l'evenement
            const tarifEvent = document.querySelector('input[name="tarif_event"]').value;

            // appel AJAX pour verifier la participation sportive
            fetch(window.location.origin + window.location.pathname.replace('index.php', '') + 'index.php?path=/membre/calculer-tarif-asso', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    tarif_event: parseFloat(tarifEvent)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tarif = parseFloat(data.tarif);
                    membreTarif = tarif;
                    membreTarifCalcule = true;

                    // affiche le tarif avec la raison
                    montantEl.textContent = tarif.toFixed(2) + ' €';
                    montantEl.classList.add('calculated');
                    if (data.raison) {
                        raisonEl.textContent = '(' + data.raison + ')';
                    }
                    
                    // affiche le résultat, cache le loading
                    loadingEl.style.display = 'none';
                    resultEl.style.display = 'flex';

                    // met a jour le total
                    updateTotal();
                    checkFormValidity();
                } else {
                    loadingEl.textContent = 'Erreur: ' + (data.message || 'Erreur inconnue');
                    loadingEl.classList.add('error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                loadingEl.textContent = 'Erreur lors du calcul du tarif';
                loadingEl.classList.add('error');
            });
            
            return;
        }

        // Pour les accompagnateurs (avec bouton)
        const tarifMontant = tarifDisplay.querySelector('.tarif-montant');

        if (!btn || !tarifMontant) return;

        // desactive le bouton pendant le calcul
        btn.disabled = true;
        btn.textContent = 'Calcul en cours...';

        // recupere le tarif de l'evenement
        const tarifEvent = document.querySelector('input[name="tarif_event"]').value;

        // appel AJAX pour verifier la participation sportive
        fetch(window.location.origin + window.location.pathname.replace('index.php', '') + 'index.php?path=/membre/calculer-tarif-asso', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                tarif_event: parseFloat(tarifEvent)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tarif = parseFloat(data.tarif);

                accompagnateursData[index].tarif = tarif;
                accompagnateursData[index].tarifCalcule = true;

                // affiche le tarif
                tarifMontant.textContent = tarif.toFixed(2) + ' €';
                if (data.raison) {
                    tarifMontant.textContent += ' (' + data.raison + ')';
                }
                tarifMontant.classList.add('calculated');

                // marque le bouton comme calcule
                btn.textContent = '✓ Calculé';
                btn.classList.add('calculated');

                // met a jour le total
                updateTotal();
                checkFormValidity();
            } else {
                showError('Erreur lors du calcul du tarif : ' + (data.message || 'Erreur inconnue'));
                btn.disabled = false;
                btn.textContent = 'Calculer';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showError('Erreur lors du calcul du tarif. Veuillez réessayer.');
            btn.disabled = false;
            btn.textContent = 'Calculer';
        });
    }

    // met a jour le montant total
    function updateTotal() {
        let total = 0;

        // ajoute le tarif du membre
        if (membreTarifCalcule) {
            total += membreTarif;
        }

        // ajoute les tarifs des accompagnateurs
        Object.values(accompagnateursData).forEach(acc => {
            if (acc.tarifCalcule) {
                total += acc.tarif;
            }
        });

        // affiche le total
        if (membreTarifCalcule || Object.values(accompagnateursData).some(acc => acc.tarifCalcule)) {
            montantTotal.textContent = total.toFixed(2) + ' €';
            montantTotal.classList.add('calculated');
        } else {
            montantTotal.textContent = '—';
            montantTotal.classList.remove('calculated');
        }

        // met a jour le comptage HelloAsso
        mettreAJourComptageHelloAsso();
    }

    // compte les personnes avec tarif > 0 pour helloasso
    function mettreAJourComptageHelloAsso() {
        let nbPersonnesAPayer = 0;

        // verifie si le membre doit payer
        if (membreTarifCalcule && membreTarif > 0) {
            nbPersonnesAPayer++;
        }

        // compte les accompagnateurs avec tarif > 0
        Object.values(accompagnateursData).forEach(acc => {
            if (acc.tarifCalcule && acc.tarif > 0) {
                nbPersonnesAPayer++;
            }
        });

        // met a jour l'affichage
        const spanNbPersonnes = document.getElementById('nb-personnes-helloasso');
        if (spanNbPersonnes) {
            spanNbPersonnes.textContent = nbPersonnesAPayer;
        }
    }

    // verifie si le formulaire peut etre soumis
    function checkFormValidity() {
        let isValid = true;

        // le membre doit avoir calcule son tarif
        if (!membreTarifCalcule) {
            isValid = false;
        }

        // tous les accompagnateurs doivent avoir calcule leur tarif
        Object.values(accompagnateursData).forEach(acc => {
            if (!acc.tarifCalcule) {
                isValid = false;
            }
        });

        // active/desactive le bouton submit
        btnSubmit.disabled = !isValid;

        // met a jour le texte du bouton
        const nbAccompagnateurs = Object.keys(accompagnateursData).length;
        if (nbAccompagnateurs === 0) {
            btnSubmit.textContent = "M'inscrire";
        } else if (nbAccompagnateurs === 1) {
            btnSubmit.textContent = "M'inscrire moi et mon accompagnateur";
        } else {
            btnSubmit.textContent = `M'inscrire moi et mes ${nbAccompagnateurs} accompagnateurs`;
        }
    }

    // ajoute un accompagnateur
    function ajouterAccompagnateur() {
        accompagnateursCount++;
        const index = `acc-${accompagnateursCount}`;

        // cree les donnees de l'accompagnateur
        accompagnateursData[index] = {
            nom: '',
            prenom: '',
            email: '',
            tarif: 0,
            tarifCalcule: false
        };

        // cree le HTML
        const html = `
            <div class="participant-row accompagnateur" data-index="${index}">
                <div class="participant-info">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" class="acc-nom" data-index="${index}" placeholder="Nom de famille" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" class="acc-prenom" data-index="${index}" placeholder="Prénom" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="acc-email" data-index="${index}" placeholder="email@exemple.com" required>
                    </div>
                </div>
                <div class="participant-actions">
                    <button type="button" class="btn-calculer" data-index="${index}">
                        Calculer
                    </button>
                    <div class="tarif-display" data-index="${index}">
                        <span class="tarif-label">Tarif :</span>
                        <span class="tarif-montant">—</span>
                    </div>
                    <button type="button" class="btn-supprimer" data-index="${index}">
                        ✕
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);

        // ajoute les event listeners
        const row = container.querySelector(`[data-index="${index}"]`);

        // bouton calculer
        row.querySelector('.btn-calculer').addEventListener('click', function() {
            const email = row.querySelector('.acc-email').value.trim();
            const nom = row.querySelector('.acc-nom').value.trim();
            const prenom = row.querySelector('.acc-prenom').value.trim();

            if (!nom || !prenom || !email) {
                showError('Veuillez remplir tous les champs avant de calculer le tarif.');
                return;
            }

            if (!validateEmail(email)) {
                showError('Veuillez saisir une adresse email valide.');
                return;
            }

            // sauvegarde les infos
            accompagnateursData[index].nom = nom;
            accompagnateursData[index].prenom = prenom;
            accompagnateursData[index].email = email;

            // calcule le tarif
            calculerTarif(email, index, false);
        });

        // bouton supprimer
        row.querySelector('.btn-supprimer').addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet accompagnateur ?')) {
                row.remove();
                delete accompagnateursData[index];
                updateTotal();
                checkFormValidity();
            }
        });

        // met a jour quand les champs changent
        row.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                // si les infos changent apres calcul, on reset le tarif
                if (accompagnateursData[index].tarifCalcule) {
                    accompagnateursData[index].tarifCalcule = false;
                    accompagnateursData[index].tarif = 0;

                    const btn = row.querySelector('.btn-calculer');
                    const tarifDisplay = row.querySelector('.tarif-display .tarif-montant');

                    btn.textContent = 'Calculer';
                    btn.classList.remove('calculated');
                    btn.disabled = false;

                    tarifDisplay.textContent = '—';
                    tarifDisplay.classList.remove('calculated');

                    updateTotal();
                    checkFormValidity();
                }
            });
        });

        checkFormValidity();
    }

    // validation email
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // event listeners principaux

    // bouton ajouter accompagnateur
    btnAjouter.addEventListener('click', ajouterAccompagnateur);

    // Calcul automatique du tarif du membre principal au chargement
    const membreTarifDisplay = document.querySelector('.tarif-membre-principal');
    if (membreTarifDisplay) {
        const email = membreTarifDisplay.getAttribute('data-email');
        calculerTarif(email, 'membre', true);
    }

    // soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // verifie que tout est calcule
        if (!membreTarifCalcule) {
            showError('Veuillez calculer votre tarif avant de soumettre le formulaire.');
            return;
        }

        const hasUncalculated = Object.values(accompagnateursData).some(acc => !acc.tarifCalcule);
        if (hasUncalculated) {
            showError('Veuillez calculer le tarif de tous les accompagnateurs avant de soumettre le formulaire.');
            return;
        }

        // met a jour les champs hidden
        nbInvitesHidden.value = Object.keys(accompagnateursData).length;
        accompagnateursDataHidden.value = JSON.stringify(accompagnateursData);

        // soumet le formulaire
        this.submit();
    });

    // initialisation
    checkFormValidity();

    // charge les accompagnateurs existants en mode edition
    function chargerAccompagnateursExistants(accompagnateurs) {
        if (!accompagnateurs || accompagnateurs.length === 0) {
            return;
        }

        accompagnateurs.forEach(acc => {
            accompagnateursCount++;
            const index = `acc-${accompagnateursCount}`;

            // cree les donnees avec les infos existantes
            accompagnateursData[index] = {
                nom: acc.Nom || '',
                prenom: acc.Prenom || '',
                email: acc.Email || '',
                tarif: parseFloat(acc.Tarif) || 0,
                tarifCalcule: true
            };

            // cree le HTML avec les valeurs pre-remplies
            const html = `
                <div class="participant-row accompagnateur" data-index="${index}">
                    <div class="participant-info">
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" class="acc-nom" data-index="${index}" placeholder="Nom de famille" required value="${accompagnateursData[index].nom}">
                        </div>
                        <div class="form-group">
                            <label>Prénom</label>
                            <input type="text" class="acc-prenom" data-index="${index}" placeholder="Prénom" required value="${accompagnateursData[index].prenom}">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="acc-email" data-index="${index}" placeholder="email@exemple.com" required value="${accompagnateursData[index].email}">
                        </div>
                    </div>
                    <div class="participant-actions">
                        <button type="button" class="btn-calculer calculated" data-index="${index}" disabled>
                            ✓ Calculé
                        </button>
                        <div class="tarif-display" data-index="${index}">
                            <span class="tarif-label">Tarif :</span>
                            <span class="tarif-montant calculated">${accompagnateursData[index].tarif.toFixed(2)} €</span>
                        </div>
                        <button type="button" class="btn-supprimer" data-index="${index}">
                            ✕
                        </button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);

            // ajoute les event listeners
            const row = container.querySelector(`[data-index="${index}"]`);

            // bouton calculer
            row.querySelector('.btn-calculer').addEventListener('click', function() {
                const email = row.querySelector('.acc-email').value.trim();
                const nom = row.querySelector('.acc-nom').value.trim();
                const prenom = row.querySelector('.acc-prenom').value.trim();

                if (!nom || !prenom || !email) {
                    showError('Veuillez remplir tous les champs avant de calculer le tarif.');
                    return;
                }

                if (!validateEmail(email)) {
                    showError('Veuillez saisir une adresse email valide.');
                    return;
                }

                // sauvegarde les infos
                accompagnateursData[index].nom = nom;
                accompagnateursData[index].prenom = prenom;
                accompagnateursData[index].email = email;

                // calcule le tarif
                calculerTarif(email, index, false);
            });

            // bouton supprimer
            row.querySelector('.btn-supprimer').addEventListener('click', function() {
                if (confirm('Êtes-vous sûr de vouloir supprimer cet accompagnateur ?')) {
                    row.remove();
                    delete accompagnateursData[index];
                    updateTotal();
                    checkFormValidity();
                }
            });

            // met a jour quand les champs changent
            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', function() {
                    // si les infos changent apres calcul, on reset le tarif
                    if (accompagnateursData[index].tarifCalcule) {
                        accompagnateursData[index].tarifCalcule = false;
                        accompagnateursData[index].tarif = 0;

                        const btn = row.querySelector('.btn-calculer');
                        const tarifDisplay = row.querySelector('.tarif-display .tarif-montant');

                        btn.textContent = 'Calculer';
                        btn.classList.remove('calculated');
                        btn.disabled = false;

                        tarifDisplay.textContent = '—';
                        tarifDisplay.classList.remove('calculated');

                        updateTotal();
                        checkFormValidity();
                    }
                });
            });
        });

        updateTotal();
        checkFormValidity();
    }

    // si mode edition, charger les accompagnateurs existants
    const formMode = form ? form.getAttribute('data-mode') : null;
    if (formMode === 'edition') {
        // recuperer le tarif du membre depuis l'attribut data
        const tarifMembreStr = form.getAttribute('data-tarif-membre');
        if (tarifMembreStr) {
            membreTarif = parseFloat(tarifMembreStr);
            membreTarifCalcule = true;

            // afficher le tarif du membre
            const membreTarifDisplay = document.querySelector('.membre-principal .tarif-display .tarif-montant');
            const membreBtnCalculer = document.querySelector('.membre-principal .btn-calculer');

            if (membreTarifDisplay && membreBtnCalculer) {
                membreTarifDisplay.textContent = membreTarif.toFixed(2) + ' €';
                membreTarifDisplay.classList.add('calculated');
                membreBtnCalculer.textContent = '✓ Calculé';
                membreBtnCalculer.classList.add('calculated');
                membreBtnCalculer.disabled = true;
            }
        }

        // recuperer les accompagnateurs depuis l'attribut data
        const accompagnateursStr = form.getAttribute('data-accompagnateurs');
        if (accompagnateursStr) {
            try {
                const accompagnateurs = JSON.parse(accompagnateursStr);
                chargerAccompagnateursExistants(accompagnateurs);
            } catch (e) {
                console.error('Erreur lors du parsing des accompagnateurs:', e);
            }
        }
    }
})();
