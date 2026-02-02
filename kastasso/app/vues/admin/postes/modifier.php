<?php
$pageTitle = "Modifier un Poste";
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">

<div class="admin-container">
    <h1>Modifier le poste</h1>

    <form action="<?= url('/admin/postes/update') ?>" method="POST" class="admin-form">
        <?= champCSRF() ?>
        <input type="hidden" name="id_poste" value="<?= htmlspecialchars($poste['Id_Poste']) ?>">

        <div class="form-group">
            <label for="libelle">Libellé du poste <span class="required">*</span></label>
            <input type="text" id="libelle" name="libelle" value="<?= htmlspecialchars($poste['libelle']) ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="niveau">Niveau de compétence requis <span class="required">*</span></label>
            <select id="niveau" name="niveau" required class="form-control">
                <option value="1" <?= $poste['niveau'] == 1 ? 'selected' : '' ?>>Niveau 1 - Débutant</option>
                <option value="2" <?= $poste['niveau'] == 2 ? 'selected' : '' ?>>Niveau 2 - Intermédiaire</option>
                <option value="3" <?= $poste['niveau'] == 3 ? 'selected' : '' ?>>Niveau 3 - Expérimenté</option>
            </select>
            <small class="form-text">Le niveau indique la complexité du poste</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?= url('/admin/postes') ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
