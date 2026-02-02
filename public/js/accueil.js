/**
 * accueil.js - Script simplifié pour la page d'accueil
 * Kast'Asso - Filtrage des événements uniquement
 */

(() => {
    'use strict';

    const elements = {
        tabs: document.querySelectorAll('.tab'),
        eventCards: document.querySelectorAll('[data-type]'),
        eventsContainer: document.querySelector('.row')
    };

    const init = () => {
        setupTabs();
    };

    // Gestion des onglets de filtrage
    const setupTabs = () => {
        elements.tabs.forEach(tab => {
            tab.addEventListener('click', handleTabClick);
        });
    };

    const handleTabClick = function() {
        const filter = this.getAttribute('data-filter');
        
        // Mise à jour de l'onglet actif
        elements.tabs.forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');

        // Filtrage des cartes
        filterCards(filter);
    };

    const filterCards = (filter) => {
        let visibleCount = 0;
        
        elements.eventCards.forEach(card => {
            const eventType = card.getAttribute('data-type');
            const shouldShow = filter === 'tous' || eventType === filter;

            if (shouldShow) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Message si aucun événement
        updateNoEventsMessage(visibleCount, filter);
    };

    // Message "Aucun événement"
    const updateNoEventsMessage = (count, filter) => {
        let msg = document.querySelector('.no-events-message');
        
        if (count === 0) {
            if (!msg) {
                msg = document.createElement('div');
                msg.className = 'no-events-message';
                msg.innerHTML = `
                    <p><strong>Aucun événement</strong></p>
                    <p class="no-events-text"></p>
                `;
                elements.eventsContainer?.parentNode?.insertBefore(msg, elements.eventsContainer.nextSibling);
            }
            
            const filterLabels = {
                sport: 'sportifs',
                asso: 'associatifs',
                tous: ''
            };
            const label = filterLabels[filter] || '';
            msg.querySelector('.no-events-text').textContent = 
                `Aucun événement ${label} disponible pour le moment.`;
            
            msg.classList.remove('hidden');
        } else if (msg) {
            msg.classList.add('hidden');
        }
    };

    // Support clavier pour accessibilité
    const setupKeyboardNavigation = () => {
        elements.tabs.forEach((tab, index) => {
            tab.setAttribute('role', 'tab');
            tab.setAttribute('tabindex', index === 0 ? '0' : '-1');
            
            tab.addEventListener('keydown', (e) => {
                let newIndex;
                
                switch (e.key) {
                    case 'ArrowLeft':
                    case 'ArrowUp':
                        e.preventDefault();
                        newIndex = index > 0 ? index - 1 : elements.tabs.length - 1;
                        break;
                    case 'ArrowRight':
                    case 'ArrowDown':
                        e.preventDefault();
                        newIndex = index < elements.tabs.length - 1 ? index + 1 : 0;
                        break;
                    default:
                        return;
                }
                
                elements.tabs[newIndex].focus();
                elements.tabs[newIndex].click();
            });
        });
    };

    // Lancement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init();
            setupKeyboardNavigation();
        });
    } else {
        init();
        setupKeyboardNavigation();
    }

})();

