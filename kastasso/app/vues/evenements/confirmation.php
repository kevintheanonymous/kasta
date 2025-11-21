<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
    <link rel="stylesheet" href="/kastasso/public/css/tableau_de_bord_admin.css">
</head>
<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <h1>Merci, votre création a été enregistrée.</h1>
    <a href="/kastasso/public/index.php?path=/admin/tableau_de_bord">Retour au tableau de bord</a>
</body>
</html>
