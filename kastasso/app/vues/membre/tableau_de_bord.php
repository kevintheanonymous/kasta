<?php

self::ensurerSession();
$userName = $_SESSION['user_name'] ?? 'Membre';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Kast'Asso</title>
    <link rel="stylesheet" href="/kastasso/public/css/tableau_de_bord_membre.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <nav>
        <div class="nav-container">
            <div class="brand">Kast'Asso</div>
            <div class="links">
                <a href="/kastasso/public/index.php?path=/membre/tableau_de_bord">Tableau de bord</a>
                <a href="/kastasso/public/index.php?path=/membre/evenements">Événements</a>
                <a href="/kastasso/public/index.php?path=/membre/profil">Profil</a>
            </div>
            <div class="logout">
                <a href="/kastasso/public/index.php?path=/deconnexion">Déconnexion</a>
            </div>
        </div>
    </nav>
</header>

<main>
    <div class="welcome-box">
        <h1>Bonjour <?= htmlspecialchars($userName) ?></h1>
        <p>Heureux de vous revoir sur Kast'Asso. Consultez les derniers événements ou mettez votre profil à jour.</p>
    </div>
</main>
</body>
</html>