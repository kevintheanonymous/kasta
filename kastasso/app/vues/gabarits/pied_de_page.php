<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>KAST'ASSO</h3>
            <p>Votre salle de sport associative dédiée à la performance et au bien-être.</p>
        </div>
        <div class="footer-section">
            <h3>Liens Rapides</h3>
            <?php if (isset($_SESSION['user'])): ?>
                <?php if (($_SESSION['user_type'] ?? '') === 'admin'): ?>
                    <a href="<?= url('/admin/tableau_de_bord') ?>">Accueil</a>
                    <a href="<?= url('/admin/events') ?>">Gérer les événements</a>
                <?php elseif (($_SESSION['user_type'] ?? '') === 'gestionnaire'): ?>
                    <a href="<?= url('/gestionnaire/tableau_de_bord') ?>">Accueil</a>
                    <a href="<?= url('/gestionnaire/events') ?>">Gérer les événements</a>
                <?php else: ?>
                    <a href="<?= url('/membre/tableau_de_bord') ?>">Accueil</a>
                    <a href="<?= url('/membre/profil') ?>">Mon profil</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= url('/') ?>">Accueil</a>
                <a href="<?= url('/inscription') ?>">Rejoignez-nous</a>
            <?php endif; ?>
        </div>
        <div class="footer-section">
            <h3>Contact</h3>
            <p>Email: contact@kastasso.fr</p>
            <p>Téléphone: 01 23 45 67 89</p>
            <p>Adresse: 123 Rue du Sport, 75000 Paris</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date("Y"); ?> Kast'Asso. Tous droits réservés.
    </div>
</footer>
<!-- Script global pour navbar mobile et utilitaires -->
<script src="<?= asset('js/global.js') ?>?v=<?= time() ?>"></script>

