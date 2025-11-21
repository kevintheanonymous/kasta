<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
<form action="/kastasso/public/index.php?path=/inscription-partie2" method="post">
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
    <input type="submit" value="Suivant">
</form>
<script src="/kastasso/public/js/formulaire_partie2.js"></script>
</body>
</html>
