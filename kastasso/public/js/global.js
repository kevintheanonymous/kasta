// global

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, { passive: true });
    }

// menu hamburger mobile
    const navbarHamburger = document.querySelector('.navbar-hamburger');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    if (navbarHamburger && navbarCollapse) {
        const toggleMenu = () => {
            const isActive = navbarHamburger.classList.contains('active');
            navbarHamburger.classList.toggle('active');
            navbarCollapse.classList.toggle('active');
            navbarHamburger.setAttribute('aria-expanded', !isActive);
        };

        const closeMenu = () => {
            navbarHamburger.classList.remove('active');
            navbarCollapse.classList.remove('active');
            navbarHamburger.setAttribute('aria-expanded', 'false');
        };

        navbarHamburger.addEventListener('click', toggleMenu);

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar')) {
                closeMenu();
            }
        });

        // Close menu when clicking on a link (non-dropdown)
        navbarCollapse.querySelectorAll('a:not(.dropdown-trigger)').forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        // Close menu on resize to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                closeMenu();
                // Also close any open dropdowns
                document.querySelectorAll('.dropdown.active').forEach(d => d.classList.remove('active'));
            }
        });

        // Handle dropdown toggles on mobile
        document.querySelectorAll('.navbar-menu .dropdown-trigger').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    const dropdown = trigger.closest('.dropdown');
                    // Close other dropdowns
                    document.querySelectorAll('.navbar-menu .dropdown.active').forEach(d => {
                        if (d !== dropdown) d.classList.remove('active');
                    });
                    dropdown.classList.toggle('active');
                }
            });
        });
    }

// animations
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in');
    if (animatedElements.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animatedElements.forEach(el => observer.observe(el));
    }

// smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

// alertes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Ajouter bouton fermer si absent
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Fermer');
            closeBtn.addEventListener('click', () => {
                alert.classList.add('fade-out');
                setTimeout(() => alert.remove(), 300);
            });
            alert.appendChild(closeBtn);
        }

        // Auto-fermeture des alertes succès après 5 secondes
        if (alert.classList.contains('alert-success')) {
            setTimeout(() => {
                alert.classList.add('fade-out');
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    });

// boutons confirmations
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Êtes-vous sûr ?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

// preload images
    document.querySelectorAll('img[loading="lazy"]').forEach(img => {
        img.addEventListener('load', function() {
            this.classList.add('loaded');
        });
    });
});
