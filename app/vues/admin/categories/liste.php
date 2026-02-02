<?php
$pageTitle = "Gestion des Catégories";
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">

<div class="admin-container">
    <h1>Gestion des Catégories</h1>

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

    <div class="actions-bar">
        <a href="<?= url('/admin/categories/create') ?>" class="btn btn-primary">Nouvelle Catégorie</a>
    </div>

    <div class="table-wrapper">
        <table class="admin-table table-categories">
            <thead>
                <tr>
                    <th class="col-libelle">Libellé</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $categorie): ?>
                    <tr>
                        <td class="col-libelle"><?= htmlspecialchars($categorie['libelle']) ?></td>
                        <td class="td-actions">
                            <div class="actions-buttons">
                                <a href="<?= url('/admin/categories/edit&id=' . $categorie['Id_Categorie_evenement']) ?>" class="btn btn-action btn-warning" title="Modifier la catégorie">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Modifier
                                </a>

                                <form action="<?= url('/admin/categories/delete') ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');" style="display: inline;">
                                    <?= champCSRF() ?>
                                    <input type="hidden" name="id_categorie" value="<?= $categorie['Id_Categorie_evenement'] ?>">
                                    <button type="submit" class="btn btn-action btn-danger" title="Supprimer la catégorie">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        Supprimer
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

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
