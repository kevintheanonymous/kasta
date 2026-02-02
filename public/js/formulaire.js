// Validation du formulaire d'inscription

(() => {
    'use strict';

    // Configuration
    const CONFIG = {
        debounceDelay: 300,
        animationDuration: 300,
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedImageTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    };

    const REGEX = {
        nomPrenom: /^[A-Za-zÀ-ÿ\s'-]+$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        telephone: /^0[1-9][0-9]{8}$/,
        minuscule: /[a-z]/,
        majuscule: /[A-Z]/,
        chiffre: /[0-9]/,
        special: /[!@#$%^&*(),.?":{}|<>]/
    };

    const MESSAGES = {
        champCourt: "Ce champ doit contenir au moins 2 lettres.",
        champLong: "Ce champ doit contenir moins de 30 caractères.",
        caractereInvalide: "Utilisez uniquement des lettres valides (pas de caractères spéciaux ni chiffres).",
        emailInvalide: "Adresse e-mail invalide.",
        telephoneInvalide: "Numéro invalide.",
        telephoneFormat: "Numéro non valide (ex: 0612345678).",
        mdpVide: "Mot de passe invalide.",
        mdpMinuscule: "Le mot de passe doit contenir au moins une lettre minuscule.",
        mdpMajuscule: "Le mot de passe doit contenir au moins une lettre majuscule.",
        mdpChiffre: "Le mot de passe doit contenir au moins un chiffre.",
        mdpSpecial: "Le mot de passe doit contenir au moins un caractère spécial.",
        mdpCourt: "Le mot de passe doit contenir au moins 8 caractères.",
        mdpDifferent: "Les mots de passe ne correspondent pas.",
        mdpCorrespond: "Les mots de passe correspondent"
    };

    // Éléments du DOM
    const elements = {
        form: document.querySelector('form'),
        champNom: document.getElementById('nom'),
        champPrenom: document.getElementById('prenom'),
        champEmail: document.getElementById('email'),
        champTelephone: document.getElementById('telephone'),
        mdp: document.getElementById('mdp'),
        confmdp: document.getElementById('confmdp'),
        submitBtn: document.getElementById('submit'),
        messageMdpConf: document.getElementById('message_mdp_conf'),
        messageMdp: document.getElementById('message_mdp'),
        verifTshirt: document.getElementById('t-shirt'),
        verifPull: document.getElementById('pull'),
        photoUpload: document.getElementById('photo-upload'),
        avatar: document.querySelector('.avatar')
    };

    // src original de l'avatar
    const originalAvatarSrc = elements.avatar?.src || '';


    // Utilitaires

    const debounce = (fn, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn(...args), delay);
        };
    };

    // État visuel d'un champ
    const setFieldState = (input, message, isValid, text = '') => {
        if (!input) return;
        
        // Classes d'état
        input.classList.toggle('valid', isValid);
        input.classList.toggle('invalid', !isValid);
        input.classList.toggle('is-valid', isValid);
        input.classList.toggle('is-invalid', !isValid);
        
        // Attributs ARIA
        input.setAttribute('aria-invalid', !isValid);
        
        // Message
        if (message) {
            message.textContent = text;
            message.className = `field-message ${isValid ? 'valid-texte success' : 'error-texte error'}`;
            message.setAttribute('role', 'alert');
            
            // Animation du message
            if (text) {
                message.style.animation = 'none';
                message.offsetHeight; // Force reflow
                message.style.animation = 'fadeInUp 0.3s ease-out';
            }
        }

        // Animation du champ en cas d'erreur
        if (!isValid && input.value) {
            input.style.animation = 'none';
            input.offsetHeight;
            input.style.animation = 'shake 0.4s ease-in-out';
        }
    };

    // Basculer la visibilité du mot de passe
    const togglePasswordVisibility = (input, button) => {
        if (!input || !button) return;
        
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        
        // Icône animée
        button.innerHTML = isPassword 
            ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
            : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        
        button.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        button.setAttribute('aria-pressed', isPassword);
        
        // Animation du bouton (conserver translateY pour le centrage vertical)
        button.style.transform = 'translateY(-50%) scale(0.9)';
        setTimeout(() => button.style.transform = 'translateY(-50%) scale(1)', 150);
    };

    // Validations des champs

    const validerNomPrenom = (input, messageId) => {
        if (!input) return true;
        
        const valeur = input.value.trim();
        const message = document.getElementById(messageId);
        
        if (valeur.length <= 1) {
            setFieldState(input, message, false, MESSAGES.champCourt);
            return false;
        }
        
        if (!REGEX.nomPrenom.test(valeur)) {
            setFieldState(input, message, false, MESSAGES.caractereInvalide);
            return false;
        }
        
        if (valeur.length > 30) {
            setFieldState(input, message, false, MESSAGES.champLong);
            return false;
        }
        
        setFieldState(input, message, true);
        return true;
    };

    const validerEmail = () => {
        if (!elements.champEmail) return true;
        
        const message = document.getElementById('message_email');
        const valeur = elements.champEmail.value.trim();
        
        if (!valeur || !REGEX.email.test(valeur)) {
            setFieldState(elements.champEmail, message, false, MESSAGES.emailInvalide);
            return false;
        }
        
        setFieldState(elements.champEmail, message, true);
        return true;
    };

    // Validation du téléphone (optionnel)
    const validerTelephone = () => {
        if (!elements.champTelephone) return true;
        
        const message = document.getElementById('message_telephone');
        const valeur = elements.champTelephone.value.trim();
        
        // Champ optionnel - valide si vide
        if (!valeur) {
            setFieldState(elements.champTelephone, message, true);
            return true;
        }
        
        if (!REGEX.telephone.test(valeur)) {
            setFieldState(elements.champTelephone, message, false, MESSAGES.telephoneFormat);
            return false;
        }
        
        setFieldState(elements.champTelephone, message, true);
        return true;
    };

    // Vérification de la force du mot de passe
    const verifierMdpFort = () => {
        if (!elements.mdp) return true;
        
        const valeur = elements.mdp.value;
        const messageMdp = elements.messageMdp;
        
        // Calcul du score de force
        let score = 0;
        if (valeur.length >= 8) score++;
        if (valeur.length >= 12) score++;
        if (REGEX.minuscule.test(valeur)) score++;
        if (REGEX.majuscule.test(valeur)) score++;
        if (REGEX.chiffre.test(valeur)) score++;
        if (REGEX.special.test(valeur)) score++;
        
        // Mise à jour de l'indicateur de force
        updatePasswordStrength(score, valeur.length);
        
        // Validations individuelles
        const validations = [
            { test: valeur !== '', message: MESSAGES.mdpVide },
            { test: REGEX.minuscule.test(valeur), message: MESSAGES.mdpMinuscule },
            { test: REGEX.majuscule.test(valeur), message: MESSAGES.mdpMajuscule },
            { test: REGEX.chiffre.test(valeur), message: MESSAGES.mdpChiffre },
            { test: REGEX.special.test(valeur), message: MESSAGES.mdpSpecial },
            { test: valeur.length >= 8, message: MESSAGES.mdpCourt }
        ];
        
        for (const validation of validations) {
            if (!validation.test) {
                setFieldState(elements.mdp, messageMdp, false, validation.message);
                return false;
            }
        }
        
        setFieldState(elements.mdp, messageMdp, true);
        return true;
    };

    // Mise à jour de l'indicateur de force du mot de passe
    const updatePasswordStrength = (score, length) => {
        let strengthBar = document.querySelector('.password-strength-bar');
        let strengthText = document.querySelector('.password-strength-text');
        
        // Créer les éléments s'ils n'existent pas
        if (!strengthBar && elements.mdp) {
            // Trouver le label (parent du wrapper password-input-wrapper)
            // Ne PAS ajouter au wrapper pour ne pas déplacer le bouton !
            const label = elements.mdp.closest('label') ||
                          elements.mdp.closest('.form-group') ||
                          elements.mdp.parentElement.parentElement;

            const strengthContainer = document.createElement('div');
            strengthContainer.className = 'password-strength';
            strengthContainer.innerHTML = '<div class="password-strength-bar"></div>';

            const textEl = document.createElement('div');
            textEl.className = 'password-strength-text';

            label.appendChild(strengthContainer);
            label.appendChild(textEl);

            strengthBar = strengthContainer.querySelector('.password-strength-bar');
            strengthText = textEl;
        }
        
        if (!strengthBar) return;
        
        // Déterminer le niveau de force
        const levels = [
            { min: 0, class: '', text: '' },
            { min: 1, class: 'weak', text: 'Faible' },
            { min: 3, class: 'fair', text: 'Moyen' },
            { min: 5, class: 'good', text: 'Bon' },
            { min: 6, class: 'strong', text: 'Excellent' }
        ];
        
        const level = length === 0 ? levels[0] : 
                      [...levels].reverse().find(l => score >= l.min) || levels[1];
        
        strengthBar.className = `password-strength-bar ${level.class}`;
        if (strengthText) strengthText.textContent = level.text;
    };

    const validerMdpConfirmation = () => {
        if (!elements.mdp || !elements.confmdp) return true;
        
        const messageMdpConf = elements.messageMdpConf;
        
        if (!elements.mdp.value) {
            setFieldState(elements.mdp, null, false);
            setFieldState(elements.confmdp, messageMdpConf, false, MESSAGES.mdpVide);
            return false;
        }
        
        if (!elements.confmdp.value) {
            setFieldState(elements.confmdp, messageMdpConf, false, MESSAGES.mdpVide);
            return false;
        }
        
        if (elements.mdp.value !== elements.confmdp.value) {
            setFieldState(elements.confmdp, messageMdpConf, false, MESSAGES.mdpDifferent);
            return false;
        }
        
        setFieldState(elements.mdp, null, true);
        setFieldState(elements.confmdp, messageMdpConf, true, MESSAGES.mdpCorrespond);
        return true;
    };

    const validerSelect = (element) => {
        if (!element) return true;
        
        const isValid = element.value !== '';
        element.classList.toggle('valid', isValid);
        element.classList.toggle('invalid', !isValid);
        element.setAttribute('aria-invalid', !isValid);
        return isValid;
    };

    // Vérification complète du formulaire
    const verifierFormulaire = () => {
        const validations = [
            elements.champNom ? validerNomPrenom(elements.champNom, 'message_nom') : true,
            elements.champPrenom ? validerNomPrenom(elements.champPrenom, 'message_prenom') : true,
            validerEmail(),
            verifierMdpFort(),
            validerMdpConfirmation()
            // Téléphone, t-shirt et pull sont optionnels
        ];
        
        const toutValide = validations.every(v => v === true);
        
        return toutValide;
    };

    // Prévisualisation de la photo
    const setupPhotoPreview = () => {
        if (!elements.photoUpload) return;
        
        elements.photoUpload.addEventListener('change', function(e) {
            if (!e.target.files || !e.target.files[0]) return;

            const file = e.target.files[0];

            // Validation de l'extension
            const allowedExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
            const fileName = file.name.toLowerCase();
            const fileExtension = fileName.substring(fileName.lastIndexOf('.'));

            if (!allowedExtensions.includes(fileExtension)) {
                showNotification('Extension de fichier non autorisée. Formats acceptés : JPG, PNG, GIF, WebP.', 'error');
                this.value = '';
                return;
            }

            // Validation du type MIME
            if (!CONFIG.allowedImageTypes.includes(file.type)) {
                showNotification('Veuillez sélectionner une image valide (JPEG, PNG, GIF ou WebP).', 'error');
                this.value = '';
                return;
            }

            // Validation de la taille
            if (file.size > CONFIG.maxFileSize) {
                showNotification('L\'image ne doit pas dépasser 5 Mo.', 'error');
                this.value = '';
                return;
            }
            
            // Prévisualisation
            const reader = new FileReader();
            reader.onload = (event) => {
                if (elements.avatar) {
                    elements.avatar.style.opacity = '0';
                    elements.avatar.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        elements.avatar.src = event.target.result;
                        elements.avatar.style.opacity = '1';
                        elements.avatar.style.transform = 'scale(1)';
                    }, 150);
                }
                
                // Créer une prévisualisation si pas d'avatar
                createImagePreview(event.target.result, file.name);
            };
            reader.readAsDataURL(file);
        });
        
        // Drag & Drop
        setupDragDrop();
    };

    // Créer la prévisualisation de l'image
    const createImagePreview = (src, filename) => {
        const wrapper = elements.photoUpload.closest('.form-group') || elements.photoUpload.parentElement;
        let preview = wrapper.querySelector('.image-preview');
        
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'image-preview';
            wrapper.appendChild(preview);
        }
        
        preview.innerHTML = `
            <img src="${src}" alt="Prévisualisation de ${filename}">
            <button type="button" class="remove-image" aria-label="Supprimer l'image">×</button>
        `;
        
        preview.style.animation = 'fadeInUp 0.3s ease-out';

        // Bouton de suppression
        preview.querySelector('.remove-image').addEventListener('click', () => {
            elements.photoUpload.value = '';
            preview.remove();
            if (elements.avatar) {
                // Restaurer l'avatar par défaut avec animation
                elements.avatar.style.opacity = '0';
                elements.avatar.style.transform = 'scale(0.8)';

                setTimeout(() => {
                    elements.avatar.src = originalAvatarSrc;
                    elements.avatar.style.opacity = '1';
                    elements.avatar.style.transform = 'scale(1)';
                }, 150);
            }
        });
    };

    // Glisser-déposer pour l'upload
    const setupDragDrop = () => {
        const dropZone = elements.photoUpload?.closest('.file-upload-wrapper') || 
                        elements.photoUpload?.closest('.form-group');
        
        if (!dropZone) return;
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('drag-over');
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('drag-over');
            });
        });
        
        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length && elements.photoUpload) {
                elements.photoUpload.files = files;
                elements.photoUpload.dispatchEvent(new Event('change'));
            }
        });
    };

    // Afficher une notification
    const showNotification = (message, type = 'info') => {
        let container = document.querySelector('.notification-container');
        
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button type="button" class="notification-close" aria-label="Fermer">×</button>
        `;
        
        container.appendChild(notification);
        
        // Animation d'entrée
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Auto-dismiss
        const dismiss = () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        };
        
        notification.querySelector('.notification-close').addEventListener('click', dismiss);
        setTimeout(dismiss, 5000);
    };

    // Configuration des boutons de visibilité du mot de passe
    const setupPasswordToggles = () => {
        const createToggleButton = (input) => {
            if (!input) return null;

            const label = input.parentElement;
            if (!label) return null;

            // Vérifier si l'input est déjà dans un wrapper
            let inputWrapper = input.parentElement;
            if (!inputWrapper.classList.contains('password-input-wrapper')) {
                // Créer un wrapper relatif autour de l'input UNIQUEMENT
                inputWrapper = document.createElement('div');
                inputWrapper.className = 'password-input-wrapper';

                // Insérer le wrapper avant l'input
                label.insertBefore(inputWrapper, input);

                // Déplacer UNIQUEMENT l'input dans le wrapper
                // Le span message reste HORS du wrapper pour ne pas affecter le positionnement du bouton
                inputWrapper.appendChild(input);
            }

            // Vérifier si le bouton existe déjà
            let button = inputWrapper.querySelector('.password-toggle');
            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'password-toggle';
                button.setAttribute('aria-label', 'Afficher le mot de passe');
                button.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                inputWrapper.appendChild(button);
            }

            return button;
        };
        
        // Créer les boutons pour les champs de mot de passe
        const btnMdp = createToggleButton(elements.mdp);
        const btnConfMdp = createToggleButton(elements.confmdp);
        
        if (btnMdp) {
            btnMdp.addEventListener('click', () => togglePasswordVisibility(elements.mdp, btnMdp));
        }
        
        if (btnConfMdp) {
            btnConfMdp.addEventListener('click', () => togglePasswordVisibility(elements.confmdp, btnConfMdp));
        }
    };

    // Initialisation
    const init = () => {
        setupPasswordToggles();

        // Événements avec délai anti-rebond
        const debouncedValidations = {
            nom: debounce(() => validerNomPrenom(elements.champNom, 'message_nom'), CONFIG.debounceDelay),
            prenom: debounce(() => validerNomPrenom(elements.champPrenom, 'message_prenom'), CONFIG.debounceDelay),
            email: debounce(validerEmail, CONFIG.debounceDelay),
            telephone: debounce(validerTelephone, CONFIG.debounceDelay)
        };
        
        if (elements.champNom) {
            elements.champNom.addEventListener('input', debouncedValidations.nom);
            elements.champNom.addEventListener('blur', () => validerNomPrenom(elements.champNom, 'message_nom'));
        }
        
        if (elements.champPrenom) {
            elements.champPrenom.addEventListener('input', debouncedValidations.prenom);
            elements.champPrenom.addEventListener('blur', () => validerNomPrenom(elements.champPrenom, 'message_prenom'));
        }
        
        if (elements.champEmail) {
            elements.champEmail.addEventListener('input', debouncedValidations.email);
            elements.champEmail.addEventListener('blur', validerEmail);
        }
        
        if (elements.champTelephone) {
            elements.champTelephone.addEventListener('input', debouncedValidations.telephone);
            elements.champTelephone.addEventListener('blur', validerTelephone);
        }
        
        if (elements.mdp) {
            elements.mdp.addEventListener('input', () => {
                verifierMdpFort();
                if (elements.confmdp && elements.confmdp.value) {
                    validerMdpConfirmation();
                }
            });
        }
        
        if (elements.confmdp) {
            elements.confmdp.addEventListener('input', validerMdpConfirmation);
        }
        
        if (elements.verifTshirt) {
            elements.verifTshirt.addEventListener('change', () => validerSelect(elements.verifTshirt));
        }
        
        if (elements.verifPull) {
            elements.verifPull.addEventListener('change', () => validerSelect(elements.verifPull));
        }

        setupPhotoPreview();

        // Soumission du formulaire
        if (elements.form) {
            elements.form.addEventListener('submit', function(event) {
                // Valider le formulaire
                const formulaireValide = verifierFormulaire();
                
                // Si non valide, afficher les erreurs mais ne pas bloquer
                if (!formulaireValide) {
                    // Focus sur le premier champ en erreur (informatif seulement)
                    const premierErreur = elements.form.querySelector('.invalid, .is-invalid');
                    if (premierErreur) {
                        premierErreur.focus();
                        premierErreur.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                // Laisser le formulaire se soumettre - la validation côté serveur vérifiera
            });
        }

        // Animation d'entrée des champs
        document.querySelectorAll('.form-group').forEach((group, index) => {
            group.style.animationDelay = `${index * 0.1}s`;
        });
    };

    // Lancement du script
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();





