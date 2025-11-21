<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire Kast'Asso</title>
    <link rel="stylesheet" href="/kastasso/public/css/formulaire.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet"> 
</head>
<body>
<h1>FORMULAIRE D'INSCRIPTION À KAST'ASSO</h1>
<form action="/kastasso/public/index.php?path=/inscription" method="post" novalidate>
    <label>Nom :
        <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_SESSION['inscription']['nom'] ?? ''); ?>">
        <span id="message_nom"></span>
    </label>
    <label>Prénom :
        <input type="text" id="prenom" name="prenom" required value="<?php echo htmlspecialchars($_SESSION['inscription']['prenom'] ?? ''); ?>">
        <span id="message_prenom"></span>
    </label>
    <label>Adresse e-mail :
        <input type="email" autocomplete="email"id="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['inscription']['email'] ?? ''); ?>">
        <span id="message_email"></span>
    </label>
    <label>Numéro de téléphone :
        <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($_SESSION['inscription']['telephone'] ?? ''); ?>">
        <span id="message_telephone"></span>
    </label>
    <label>Mot de passe :
        <input type="password" id="mdp" name="mot_de_passe" required>
        <button type="button" id="voirmdp">👀️</button>
        <span id="message_mdp"></span>
    </label>
    <label>Confirmer mot de passe :
        <input type="password" id="confmdp" name="confirmer_mdp" required>
        <button type="button" id="voirmdp2" >👀️</button>
        <span id="message_mdp_conf"></span>
    </label>
    <input id='submit' type="submit" value="Suivant">
</form>
<script src="/kastasso/public/js/formulaire.js"></script>
</body>
</html>

