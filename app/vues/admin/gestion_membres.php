<?php
$membres = $membres ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Membres - Admin</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/tableau_de_bord_admin.css?v=' . time()) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>
    
    <div class="admin-container">
        <a href="<?= url('/admin/tableau_de_bord') ?>" class="back-link">
            ← Retour au tableau de bord
        </a>
        
        <h1>Gestion des Membres</h1>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['errors'])): ?>
            <?php foreach($_SESSION['errors'] as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <!-- Barre de recherche -->
        <div class="search-box">
            <input type="text" id="searchMembres" placeholder="Rechercher par nom, prénom ou email..." class="search-input">
            
            <span class="search-results-count" id="searchResultsCount"></span>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($membres as $membre): ?>
                    <tr>
                        <td data-label="Nom"><?= htmlspecialchars($membre['Nom']) ?></td>
                        <td data-label="Prénom"><?= htmlspecialchars($membre['Prenom']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($membre['Mail']) ?></td>
                        <td data-label="Statut">
                            <span class="badge badge-<?= $membre['Statut_compte'] === 'valide' ? 'success' : ($membre['Statut_compte'] === 'en_attente' ? 'warning' : 'danger') ?>">
                                <?= htmlspecialchars($membre['Statut_compte']) ?>
                            </span>
                        </td>
                        <td data-label="Rôle">
                            <?= $membre['Gestionnaire'] == 1 ? '<strong>Gestionnaire</strong>' : 'Membre' ?>
                        </td>
                        <td data-label="Actions">
                            <div class="actions-cell">
                                <?php if($membre['Statut_compte'] === 'valide'): ?>
                                    <form action="<?= url('/admin/rendre_gestionnaire') ?>" method="post" class="form-inline">
                                        <?= champCSRF() ?>
                                        <input type="hidden" name="id_membre" value="<?= $membre['Id_Membre'] ?>">
                                        <?php if($membre['Gestionnaire'] == 1): ?>
                                            <button type="submit" name="action" value="retirer" class="btn btn-sm btn-warning" title="Rétrograder en membre" onclick="return confirm('Êtes-vous sûr de vouloir rétrograder ce gestionnaire en simple membre ?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                </svg>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="action" value="ajouter" class="btn btn-sm btn-success" title="Promouvoir en gestionnaire" onclick="return confirm('Êtes-vous sûr de vouloir promouvoir ce membre en gestionnaire ?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted-badge" title="Actions indisponibles pour les comptes non validés">—</span>
                                <?php endif; ?>

                                <a href="<?= url('/admin/membre/detail&id=' . $membre['Id_Membre']) ?>"
                                   class="btn btn-sm btn-info"
                                   title="Consulter le profil">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                <a href="<?= url('/admin/membre/historique&id=' . $membre['Id_Membre']) ?>"
                                   class="btn btn-sm btn-primary"
                                   title="Consulter l'historique">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                </a>

                                <form action="<?= url('/admin/membre/supprimer') ?>"
                                      method="post"
                                      class="form-inline"
                                      onsubmit="return confirm('Supprimer définitivement ce membre ?\n\n<?= htmlspecialchars($membre['Prenom'] . ' ' . $membre['Nom'], ENT_QUOTES) ?>\n<?= htmlspecialchars($membre['Mail'], ENT_QUOTES) ?>\n\nCette action est irréversible.');">
                                    <?= champCSRF() ?>
                                    <input type="hidden" name="id_membre" value="<?= $membre['Id_Membre'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer définitivement">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
    // Recherche en temps réel dans la liste des membres
    document.getElementById('searchMembres').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.table tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const nom = row.cells[0].textContent.toLowerCase();
            const prenom = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            
            if (nom.includes(searchTerm) || prenom.includes(searchTerm) || email.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Afficher le nombre de résultats
        const countEl = document.getElementById('searchResultsCount');
        if (searchTerm) {
            countEl.textContent = visibleCount + ' résultat' + (visibleCount > 1 ? 's' : '');
        } else {
            countEl.textContent = '';
        }
    });
    </script>
    
    <?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>
