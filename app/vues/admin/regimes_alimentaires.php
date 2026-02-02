<?php
$pageTitle = "Gestion des Régimes Alimentaires";
require_once __DIR__ . '/../gabarits/en_tete.php';
require_once __DIR__ . '/../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">

<div class="admin-container">
    <h1>Gestion des Régimes Alimentaires</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
            <?php unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <a href="<?= url('/admin/tableau_de_bord') ?>" class="back-link">
        ← Retour au tableau de bord
    </a>

    <!-- Formulaire d'ajout -->
    <div class="add-form-card">
        <div class="card-header">
            <h2>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Ajouter un régime alimentaire
            </h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/admin/regimes-alimentaires/ajouter') ?>" method="POST" class="add-form">
                <?= champCSRF() ?>
                <div class="form-group-inline">
                    <input type="text" name="nom" placeholder="Nom du régime (ex: Végétarien, Sans gluten...)" required class="form-control" maxlength="100">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des régimes -->
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du régime</th>
                    <th>Membres</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($regimes)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucun régime alimentaire enregistré.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($regimes as $regime): ?>
                        <tr>
                            <td><?= $regime['id'] ?></td>
                            <td>
                                <span id="nom-<?= $regime['id'] ?>"><?= htmlspecialchars($regime['nom']) ?></span>
                                <form id="form-<?= $regime['id'] ?>" action="<?= url('/admin/regimes-alimentaires/modifier') ?>" method="POST" class="edit-form" style="display: none;">
                                    <?= champCSRF() ?>
                                    <input type="hidden" name="id" value="<?= $regime['id'] ?>">
                                    <input type="text" name="nom" value="<?= htmlspecialchars($regime['nom']) ?>" class="form-control-inline" required maxlength="100">
                                    <button type="submit" class="btn-inline btn-inline-success" title="Valider">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn-inline btn-inline-cancel" onclick="annulerModification(<?= $regime['id'] ?>)" title="Annuler">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $regime['nb_membres'] ?></span>
                            </td>
                            <td>
                                <?php
                                if (!empty($regime['date_creation'])) {
                                    $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $regime['date_creation']);
                                    echo $dateObj ? $dateObj->format('d/m/Y H:i') : 'N/A';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td class="td-actions">
                                <div class="actions-buttons">
                                    <button type="button" class="btn btn-action btn-warning" onclick="afficherModification(<?= $regime['id'] ?>)" title="Modifier">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <?php if ($regime['nb_membres'] == 0): ?>
                                        <form action="<?= url('/admin/regimes-alimentaires/supprimer') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce régime alimentaire ?');">
                                            <?= champCSRF() ?>
                                            <input type="hidden" name="id" value="<?= $regime['id'] ?>">
                                            <button type="submit" class="btn btn-action btn-danger" title="Supprimer">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-action btn-secondary" disabled title="Régime utilisé par des membres">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function afficherModification(regimeId) {
    const nomSpan = document.getElementById('nom-' + regimeId);
    const formEdit = document.getElementById('form-' + regimeId);
    if (nomSpan && formEdit) {
        nomSpan.style.display = 'none';
        formEdit.style.display = 'flex';
        formEdit.querySelector('input[name="nom"]').focus();
    }
}

function annulerModification(regimeId) {
    const nomSpan = document.getElementById('nom-' + regimeId);
    const formEdit = document.getElementById('form-' + regimeId);
    if (nomSpan && formEdit) {
        nomSpan.style.display = 'inline';
        formEdit.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
