<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kast'Asso - Gestion d'association et d'événements sportifs">
    <meta name="theme-color" content="#1e90ff">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : "Kast'Asso" ?></title>
    <!-- Variables CSS globales - doit être chargé en premier -->
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>?v=<?= time() ?>">

    <!-- Fichiers CSS de base -->
    <link rel="stylesheet" href="<?= asset('css/1-base/utilitaires.css') ?>?v=<?= time() ?>">

    <!-- Composants réutilisables -->
    <link rel="stylesheet" href="<?= asset('css/3-composants/boutons.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/3-composants/badges.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/3-composants/alertes.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/3-composants/tableaux.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/3-composants/cartes.css') ?>?v=<?= time() ?>">

    <!-- Modules métier -->
    <link rel="stylesheet" href="<?= asset('css/4-modules/creneaux.css') ?>?v=<?= time() ?>">

    <!-- Layout et pages (chargés après les composants) -->
    <link rel="stylesheet" href="<?= asset('css/gabarit.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css') ?>?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="<?= asset('js/date_fr.js') ?>?v=<?= time() ?>" defer></script>
</head>
<body>
