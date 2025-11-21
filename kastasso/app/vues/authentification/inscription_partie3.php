<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$profileImage = $_SESSION['inscription']['url_photo'] ?? '/kastasso/public/img/avatar.jpg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire Kast'Asso</title>
    <link rel="stylesheet" href="/kastasso/public/css/formulaire.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet"> 
</head>
<body>
<h1>FORMULAIRE D'INSCRIPTION À KAST'ASSO</h1>
    <div>
        <img src="<?php echo htmlspecialchars($profileImage); ?>" class="avatar" alt="Avatar">
        <label for="photo-upload" class="upload-label">Ajouter photo de profil</label>
    </div>
<form action="/kastasso/public/index.php?path=/inscription-partie3" method="post" enctype="multipart/form-data">
    <input type="file" id="photo-upload" name="photo" accept="image/*" style="display:none;">
    <label>Nom :
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($_SESSION['inscription']['nom'] ?? ''); ?>">
        <span id="message_nom"></span>
    </label>
    <label>Prénom :
        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($_SESSION['inscription']['prenom'] ?? ''); ?>">
        <span id="message_prenom"></span>
    </label>
    <label>Adresse e-mail :
                <input type="email" autocomplete="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['inscription']['mail'] ?? ''); ?>">
        <span id="message_email"></span>
    </label>
    <label>Numéro de téléphone :
        <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($_SESSION['inscription']['telephone'] ?? ''); ?>">
        <span id="message_telephone"></span>
    </label>
    <label>Taille t-shirt :
        <select name="taille_teeshirt" id="t-shirt">
            <option value="">SÉLECTIONNER</option>
            <option value="XS" <?php if(($_SESSION['inscription']['taille_teeshirt'] ?? '')=="XS") echo 'selected'; ?>>XS</option>
            <option value="S" <?php if(($_SESSION['inscription']['taille_teeshirt'] ?? '')=="S") echo 'selected'; ?>>S</option>
            <option value="M" <?php if(($_SESSION['inscription']['taille_teeshirt'] ?? '')=="M") echo 'selected'; ?>>M</option>
            <option value="L" <?php if(($_SESSION['inscription']['taille_teeshirt'] ?? '')=="L") echo 'selected'; ?>>L</option>
            <option value="XL" <?php if(($_SESSION['inscription']['taille_teeshirt'] ?? '')=="XL") echo 'selected'; ?>>XL</option>
            <option value="XXL" <?php if(($_SESSION['inscription']['taille_teeshirt'] ?? '')=="XXL") echo 'selected'; ?>>XXL</option>
            <option value="3XL" <?php if(($_SESSION['inscription']['taille_teeshirt'] ?? '')=="3XL") echo 'selected'; ?>>3XL</option>
        </select>
    </label>
    <label>Taille pull :
        <select name="taille_pull" id="pull">
            <option value="">SÉLECTIONNER</option>
            <option value="XS" <?php if(($_SESSION['inscription']['taille_pull'] ?? '')=="XS") echo 'selected'; ?>>XS</option>
            <option value="S" <?php if(($_SESSION['inscription']['taille_pull'] ?? '')=="S") echo 'selected'; ?>>S</option>
            <option value="M" <?php if(($_SESSION['inscription']['taille_pull'] ?? '')=="M") echo 'selected'; ?>>M</option>
            <option value="L" <?php if(($_SESSION['inscription']['taille_pull'] ?? '')=="L") echo 'selected'; ?>>L</option>
            <option value="XL" <?php if(($_SESSION['inscription']['taille_pull'] ?? '')=="XL") echo 'selected'; ?>>XL</option>
            <option value="XXL" <?php if(($_SESSION['inscription']['taille_pull'] ?? '')=="XXL") echo 'selected'; ?>>XXL</option>
            <option value="3XL" <?php if(($_SESSION['inscription']['taille_pull'] ?? '')=="3XL") echo 'selected'; ?>>3XL</option>
        </select>
    </label>
    <label>Commentaires :
        <textarea name="commentaires" rows="4" cols="50" maxlength="500"
        placeholder="Précisez vos allergies ou régimes spécifiques ici"><?php echo htmlspecialchars($_SESSION['inscription']['commentaires'] ?? ''); ?></textarea>
    </label>
    <label class="checkbox-adherent">
        <input type="checkbox" name="adherent" value="1"
        
        <?php if (!empty($_SESSION['inscription']['adherent'])) echo 'checked'; ?>>
         Souhaitez-vous devenir adhérent(e) ?
    </label>
    <input id="submit" type="submit" name="action" value="Confirmer l'inscription">
    <input class="modification" type="submit" name="action" value="Annuler">
</form>
<script src="/kastasso/public/js/formulaire_partie3.js"></script>
</body>
</html>
