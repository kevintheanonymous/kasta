<?php
$pageTitle = "Modifier une Catégorie";
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">

<div class="admin-container">
    <h1>Modifier la catégorie</h1>

    <form action="<?= url('/admin/categories/update') ?>" method="POST" class="admin-form">
        <?= champCSRF() ?>
        <input type="hidden" name="id_categorie" value="<?= htmlspecialchars($categorie['Id_Categorie_evenement']) ?>">
        
        <div class="form-group">
            <label for="libelle">Libellé de la catégorie</label>
            <input type="text" id="libelle" name="libelle" value="<?= htmlspecialchars($categorie['libelle']) ?>" required class="form-control">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?= url('/admin/categories') ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
