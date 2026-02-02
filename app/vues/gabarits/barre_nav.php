<nav class="navbar">
    <div class="navbar-logo">
        <?php if (isset($_SESSION['user'])): ?>
            <?php if (($_SESSION['user_type'] ?? '') === 'admin'): ?>
                <a href="<?= url('/admin/tableau_de_bord') ?>">KAST'<span>ASSO</span></a>
            <?php elseif (($_SESSION['user_type'] ?? '') === 'gestionnaire'): ?>
                <a href="<?= url('/gestionnaire/tableau_de_bord') ?>">KAST'<span>ASSO</span></a>
            <?php else: ?>
                <a href="<?= url('/membre/tableau_de_bord') ?>">KAST'<span>ASSO</span></a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= url('/') ?>">KAST'<span>ASSO</span></a>
        <?php endif; ?>
    </div>

    <!-- Hamburger Menu Button -->
    <button class="navbar-hamburger" id="navbar-hamburger" aria-label="Menu" aria-expanded="false">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>

    <div class="navbar-collapse" id="navbar-collapse">
        <ul class="navbar-menu">
        <?php if (isset($_SESSION['user'])): ?>
            <?php if (($_SESSION['user_type'] ?? '') === 'admin'): ?>
                <li><a href="<?= url('/admin/tableau_de_bord') ?>">Tableau de bord</a></li>
            <?php elseif (($_SESSION['user_type'] ?? '') === 'gestionnaire'): ?>
                <li><a href="<?= url('/gestionnaire/tableau_de_bord') ?>">Tableau de bord</a></li>
            <?php else: ?>
                <li><a href="<?= url('/membre/tableau_de_bord') ?>">Accueil</a></li>
            <?php endif; ?>
        <?php else: ?>
            <li><a href="<?= url('/') ?>">Accueil</a></li>
            <li><a href="<?= url('/contact') ?>">Nous contacter</a></li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
            <li class="dropdown">
                <a href="<?= url('/admin/events') ?>" class="dropdown-trigger">Événements</a>
                <div class="dropdown-content">
                    <a href="<?= url('/admin/events') ?>">Gérer les événements</a>
                    <a href="<?= url('/admin/events/create-with-slots&type=sport') ?>">+ Créer Sportif</a>
                    <a href="<?= url('/admin/events/create&type=asso') ?>">+ Créer Associatif</a>
                </div>
            </li>

            <li class="dropdown">
                <a href="<?= url('/admin/membres') ?>" class="dropdown-trigger">Membres</a>
                <div class="dropdown-content">
                    <a href="<?= url('/admin/membres') ?>">Liste des membres</a>
                    <a href="<?= url('/admin/adhesions') ?>">Demandes d'adhésion</a>
                </div>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-trigger">Paramètres</a>
                <div class="dropdown-content">
                    <a href="<?= url('/admin/categories') ?>">Catégories d'événements</a>
                    <a href="<?= url('/admin/postes') ?>">Postes & Rôles</a>
                    <a href="<?= url('/admin/template-adhesion') ?>">Template adhésion</a>
                </div>
            </li>

        <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'gestionnaire'): ?>
            
            <li class="dropdown">
                <a href="<?= url('/gestionnaire/events') ?>" class="dropdown-trigger">Gestion Événements</a>
                <div class="dropdown-content">
                    <a href="<?= url('/gestionnaire/events') ?>">Gérer les événements</a>
                    <a href="<?= url('/gestionnaire/events/create-with-slots&type=sport') ?>">+ Créer Sportif</a>
                    <a href="<?= url('/gestionnaire/events/create&type=asso') ?>">+ Créer Associatif</a>
                </div>
            </li>

            <li><a href="<?= url('/gestionnaire/adhesions') ?>">Demandes d'adhésion</a></li>

            <li class="dropdown">
                <a href="#" class="dropdown-trigger">Mes Inscriptions</a>
                <div class="dropdown-content">
                    <a href="<?= url('/membre/mes_inscriptions_sport') ?>">Événements Sportifs</a>
                    <a href="<?= url('/membre/mes_inscriptions_asso') ?>">Événements Associatifs</a>
                </div>
            </li>

            <li><a href="<?= url('/membre/mes-evenements-passes') ?>">Historique</a></li>
            
        <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'membre'): ?>
            <li class="dropdown">
                <a href="#" class="dropdown-trigger">Mes Inscriptions</a>
                <div class="dropdown-content">
                    <a href="<?= url('/membre/mes_inscriptions_sport') ?>">Événements Sportifs</a>
                    <a href="<?= url('/membre/mes_inscriptions_asso') ?>">Événements Associatifs</a>
                </div>
            </li>
            <li><a href="<?= url('/membre/mes-evenements-passes') ?>">Historique</a></li>
            <li>
                <a href="<?= url('/membre/contact') ?>" class="nav-link">Nous contacter</a>
            </li>
        <?php endif; ?>
        </ul>

        <div class="navbar-auth">
            <?php if (isset($_SESSION['user'])): ?>
                <?php if (($_SESSION['user_type'] ?? '') !== 'admin'): ?>
                    <a href="<?= url('/membre/profil') ?>" class="nav-profile-link" title="Voir mon profil">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Mon Profil
                    </a>
                <?php endif; ?>
                <a href="<?= url('/deconnexion') ?>" class="btn-nav btn-nav-logout">Déconnexion</a>
            <?php else: ?>
                <a href="<?= url('/connexion') ?>" class="btn-nav btn-nav-login">Connexion</a>
                <a href="<?= url('/inscription') ?>" class="btn-nav btn-nav-signup">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</nav>