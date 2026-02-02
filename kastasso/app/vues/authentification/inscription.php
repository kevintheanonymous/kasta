<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Inscription - Kast'Asso";
require_once __DIR__ . '/../gabarits/en_tete.php';
require_once __DIR__ . '/../gabarits/barre_nav.php';
?>

<link rel="stylesheet" href="<?= asset('css/formulaires.css') ?>?v=<?= time() ?>">
<link rel="stylesheet" href="<?= asset('css/inscription.css') ?>?v=<?= time() ?>">
<link rel="stylesheet" href="<?= asset('css/adhesion_popup.css') ?>">


<main>
<?php if (isset($_SESSION['success'])): ?>
    <div class="auth-wrapper">
        <div class="auth-container inscription-success-container">
            <h1>Inscription réussie !</h1>
            
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            
            <p>
                Merci, votre compte est en cours d'attente de confirmation.<br>
                Un message vous sera envoyé une fois celui-ci validé ou refusé.
            </p>
            
            <a href="<?= url('/') ?>" class="btn btn-primary">Retour à l'accueil</a>
            
            <?php unset($_SESSION['success']); ?>
        </div>
    </div>
<?php else: ?>

    <h1>FORMULAIRE D'INSCRIPTION À KAST'ASSO</h1>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach($_SESSION['errors'] as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="<?= url('/inscription') ?>" method="post" enctype="multipart/form-data" novalidate>
        <?= champCSRF() ?>
        
        <!-- Partie 1 : Informations Personnelles -->
        <fieldset>
            <legend>Informations Personnelles</legend>
            <label>Nom :
                <input type="text" id="nom" name="nom" required placeholder="Nom" value="<?php echo htmlspecialchars($_SESSION['inscription']['nom'] ?? ''); ?>">
                <span id="message_nom"></span>
            </label>
            <label>Prénom :
                <input type="text" id="prenom" name="prenom" required placeholder="Prenom" value="<?php echo htmlspecialchars($_SESSION['inscription']['prenom'] ?? ''); ?>">
                <span id="message_prenom"></span>
            </label>
            <label>Sexe :
                <select name="sexe" id="sexe" required>
                    <option value="">SÉLECTIONNER</option>
                    <option value="H" <?php echo (($_SESSION['inscription']['sexe'] ?? '') === 'H') ? 'selected' : ''; ?>>Homme</option>
                    <option value="F" <?php echo (($_SESSION['inscription']['sexe'] ?? '') === 'F') ? 'selected' : ''; ?>>Femme</option>
                </select>
            </label>
            <label>Adresse e-mail :
                <input type="email" autocomplete="email" id="email" name="email" required placeholder="nomprenom@gmail.com" value="<?php echo htmlspecialchars($_SESSION['inscription']['mail'] ?? ''); ?>">
                <span id="message_email"></span>
            </label>
            <label>Numéro de téléphone :
                <input type="tel" id="telephone" name="telephone" placeholder="0612345678" value="<?php echo htmlspecialchars($_SESSION['inscription']['telephone'] ?? ''); ?>">
                <span id="message_telephone"></span>
            </label>
            <label>Mot de passe (1 caractère spécial, 1 chiffre et 1 majuscule) :
                <input type="password" id="mdp" name="mot_de_passe" required placeholder="Minimum 8 caractères">
                <span id="message_mdp"></span>
            </label>
            <label>Confirmer mot de passe :
                <input type="password" id="confmdp" name="confirmer_mdp" required placeholder="Confirmez votre mot de passe">
                <span id="message_mdp_conf"></span>
            </label>
        </fieldset>

        <!-- Partie 2 : Informations Complémentaires -->
        <fieldset>
            <legend>Informations Complémentaires</legend>
            <label>Taille t-shirt :
                <select name="taille_teeshirt" id="t-shirt">
                    <option value="">SÉLECTIONNER</option>
                    <?php 
                    $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
                    foreach($sizes as $size) {
                        $selected = ($_SESSION['inscription']['taille_teeshirt'] ?? '') == $size ? 'selected' : '';
                        echo "<option value=\"$size\" $selected>$size</option>";
                    }
                    ?>
                </select>
            </label>
            <label>Taille pull :
                <select name="taille_pull" id="pull">
                    <option value="">SÉLECTIONNER</option>
                    <?php 
                    foreach($sizes as $size) {
                        $selected = ($_SESSION['inscription']['taille_pull'] ?? '') == $size ? 'selected' : '';
                        echo "<option value=\"$size\" $selected>$size</option>";
                    }
                    ?>
                </select>
            </label>
            <label>Régime alimentaire :
                <select name="regime_alimentaire" id="regime_alimentaire">
                    <option value="">Aucun régime particulier</option>
                    <?php
                    if (!empty($regimesAlimentaires)) {
                        foreach ($regimesAlimentaires as $regime) {
                            $selected = (isset($_SESSION['inscription']['regime_id']) && $_SESSION['inscription']['regime_id'] == $regime['id']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($regime['id']) . '" ' . $selected . '>' . htmlspecialchars($regime['nom']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <small class="field-hint">
                    Sélectionnez votre régime alimentaire principal si vous en avez un
                </small>
            </label>
            <label>Commentaires alimentaires (Allergies, précisions...) :
                <textarea name="commentaires" rows="4" cols="50" maxlength="500" required
                placeholder="Si aucune allergie ou régime, laisser 'Aucun'"><?php echo htmlspecialchars($_SESSION['inscription']['commentaires'] ?? 'Aucun'); ?></textarea>
                <small class="field-hint">
                    Si vous n'avez aucune allergie ou régime particulier, laissez le message par défaut "Aucun"
                </small>
            </label>
        </fieldset>

        <!-- Partie 3 : Photo de profil -->
        <fieldset>
            <legend>Photo de profil</legend>

            <div class="photo-section">
                <div class="avatar-container">
                    <img src="<?= asset('img/avatar.jpg') ?>" class="avatar" alt="Aperçu">
                </div>
                <label for="photo-upload" class="upload-label">Ajouter une photo de profil</label>
                <input type="file" id="photo-upload" name="photo" accept="image/*">
            </div>
        </fieldset>

        <!-- Partie 4 : Adhésion -->
        <fieldset>
            <legend>Devenir adhérent</legend>

            <p class="adhesion-info">
                Pour devenir adhérent, vous devez télécharger le formulaire d'adhésion, le remplir et le déposer ici.
                Vous recevrez un mail en cas d'acceptation ou de refus.
            </p>

            <div class="adhesion-actions">
                <a href="<?= url('/documents/formulaire-adhesion') ?>" class="btn-download-adhesion">
                    Télécharger le formulaire d'adhésion
                </a>

                <button type="button" id="btn-deposer-adhesion" class="btn-deposer-adhesion">
                    Déposer mon formulaire
                </button>
            </div>

            <input type="file" id="formulaire-adhesion" name="formulaire_adhesion" accept=".pdf,.jpg,.jpeg" class="file-input-hidden">
            <div id="file-status" class="file-status"></div>

            <p class="adhesion-warning">
                <strong>⚠️ Attention :</strong> En tant qu'adhérent, vous serez couvert par l'assurance de l'association
                en cas de problème survenant lors d'un événement organisé. Sans adhésion, vous ne serez PAS couvert.
            </p>
        </fieldset>

        <input id='submit' type="submit" value="S'inscrire">

        <div class="form-footer">
            Vous avez déjà un compte ? <a href="<?= url('/connexion') ?>">Connectez-vous</a>
        </div>
    </form>

    <!-- Scripts JS -->
    <script src="<?= asset('js/formulaire.js') ?>"></script>
    <script src="<?= asset('js/adhesion.js') ?>"></script>
<?php endif; ?>
</main>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>

