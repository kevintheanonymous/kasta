// script auth

document.addEventListener('DOMContentLoaded', function() {

    // toggle visibilite mdp
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

    // cree bouton toggle
    const createToggleButton = (input) => {
        if (!input) return null;

        const label = input.parentElement;
        if (!label) return null;

        // Vérifier si l'input est déjà dans un wrapper
        let inputWrapper = input.parentElement;
        if (!inputWrapper.classList.contains('password-wrapper')) {
            // Créer un wrapper relatif autour de l'input UNIQUEMENT
            inputWrapper = document.createElement('div');
            inputWrapper.className = 'password-wrapper';

            // Insérer le wrapper avant l'input
            label.insertBefore(inputWrapper, input);

            // Déplacer UNIQUEMENT l'input dans le wrapper
            inputWrapper.appendChild(input);
        }

        // Vérifier si le bouton existe déjà (match CSS class name)
        let button = inputWrapper.querySelector('.password-toggle-btn');
        if (!button) {
            button = document.createElement('button');
            button.type = 'button';
            button.className = 'password-toggle-btn';
            button.setAttribute('aria-label', 'Afficher le mot de passe');
            button.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
            inputWrapper.appendChild(button);
        }

        return button;
    };

    // init champ mdp
    const mdpInput = document.getElementById('mdp');
    if (mdpInput) {
        const btnMdp = createToggleButton(mdpInput);
        if (btnMdp) {
            btnMdp.addEventListener('click', () => togglePasswordVisibility(mdpInput, btnMdp));
        }
    }

    // champ confirmation mdp
    const confMdpInput = document.getElementById('confmdp');
    if (confMdpInput) {
        const btnConfMdp = createToggleButton(confMdpInput);
        if (btnConfMdp) {
            btnConfMdp.addEventListener('click', () => togglePasswordVisibility(confMdpInput, btnConfMdp));
        }
    }
});
document.getElementById('btn-deposer-adhesion')?.addEventListener('click', function() {
        document.getElementById('formulaire-adhesion').click();
    });