<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kast'Asso</title>
    <link rel="stylesheet" href="/kastasso/public/css/tableau_de_bord_admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <h1>Tableau de Gestion des Inscriptions</h1>
    <p>Bienvenue <?= htmlspecialchars($_SESSION['user_name']) ?> | <a href="/kastasso/public/index.php?path=/deconnexion">Déconnexion</a></p>
    
    <?php
    if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']);
    endif;
    
    if (isset($_SESSION['errors'])):
        foreach($_SESSION['errors'] as $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach;
        unset($_SESSION['errors']);
    endif;
    ?>
     <div class="container">
        <div class="quick-actions">
            <h2>Actions Rapides</h2>
            <a href="/kastasso/public/index.php?path=/admin_event_sportif" class="btn btn-primary">➕ Créer Événement Sportif</a>
            <a href="/kastasso/public/index.php?path=/admin_event_associatif" class="btn btn-orange">➕ Créer Événement Associatif</a>
        </div>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Date demande</th>
                <th>Adhérent</th>
                <th>AccepterO/N</th>
                <th>Gestionnaire O/N</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($membresEnAttente) > 0):
                foreach($membresEnAttente as $membre): ?>
                    <tr>
                        <td><?= htmlspecialchars($membre['nom']) ?></td>
                        <td><?= htmlspecialchars($membre['prenom']) ?></td>
                        <td><?= htmlspecialchars($membre['mail']) ?></td>
                        <td><?= htmlspecialchars($membre['telephone']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($membre['date_statut'])) ?></td>
                        <td><?= $membre['adherent'] ? 'Oui' : 'Non' ?></td>
                        <td>
                            <form method="post" action="/kastasso/public/index.php?path=/admin/valider">
                                <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">
                                <button type="submit" name="action" value="accepter">Accepter</button>
                            </form>
                            <form method="post" action="/kastasso/public/index.php?path=/admin/refuser">
                                <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">
                                
                                <button type="submit" name="action" value="refuser">Refuser</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="/kastasso/public/index.php?path=/admin/rendre_gestionnaire">
                                <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">
        <?php if (empty($membre['gestionnaire'])): ?>
            <button type="submit" name="action" value="ajouter">Rendre gestionnaire</button>
        <?php else: ?>
            <button type="submit" name="action" value="retirer">Enlever gestionnaire</button>
        <?php endif; ?>
    </form>
</td>

                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Aucune inscription en attente</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <section class="section">
            <h2 class="section-title">Événements créés</h2>
            <?php
            require_once __DIR__ . '/../../models/Evenement.php';
            $eventModel = new Evenement();
            $evenements = $eventModel->getTousLesEvenements();
            
            if (empty($evenements)): ?>
                <p>Aucun événement créé pour le moment.</p>
            <?php else: ?>
                <div class="admin-events-list">
                    <?php foreach ($evenements as $event): 
                        $typeClass = $event['type'] === 'associatif' ? 'associatif' : '';
                    ?>
                        <div class="admin-event-item <?= $typeClass ?>">
                            <h3><?= htmlspecialchars($event['Titre']) ?></h3>
                            <p><strong>Type :</strong> <?= ucfirst($event['type']) ?></p>
                            <p><strong>Adresse :</strong> <?= htmlspecialchars($event['Adresse']) ?></p>
                            <p><strong>Date de visibilité :</strong> <?= date('d/m/Y H:i', strtotime($event['Date_Visibilite'])) ?></p>
                            
                            <?php if (!empty($event['Date_Cloture'])): ?>
                                <p><strong>Date de clôture :</strong> <?= date('d/m/Y H:i', strtotime($event['Date_Cloture'])) ?></p>
                            <?php endif; ?>
                            
                            <?php if ($event['type'] === 'sportif' && !empty($event['categorie'])): ?>
                                <p><strong>Catégorie :</strong> <?= htmlspecialchars($event['categorie']) ?></p>
                            <?php endif; ?>
                            
                            <?php if ($event['type'] === 'associatif'): ?>
                                <p><strong>Date de l'événement :</strong> <?= date('d/m/Y', strtotime($event['Date_Evenement'])) ?></p>
                                <p><strong>Tarif :</strong> <?= number_format($event['Tarif'], 2) ?> €</p>
                                <p><strong>Privé :</strong> <?= $event['Prive'] ? 'Oui' : 'Non' ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
</body>
</html>