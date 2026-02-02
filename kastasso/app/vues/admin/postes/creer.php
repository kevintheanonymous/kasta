<?php
$pageTitle = "Créer un Poste";
require_once __DIR__ . '/../../gabarits/en_tete.php';
require_once __DIR__ . '/../../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">

<div class="admin-container">
    <h1>Créer un nouveau poste</h1>

    <form action="<?= url('/admin/postes/store') ?>" method="POST" class="admin-form">
        <?= champCSRF() ?>

        <div class="form-group">
            <label for="libelle">Libellé du poste <span class="required">*</span></label>
            <input type="text" id="libelle" name="libelle" required class="form-control" placeholder="Ex: Buvette, Judging, Sécurité...">
        </div>

        <div class="form-group">
            <label for="niveau">Niveau de compétence requis <span class="required">*</span></label>
            <select id="niveau" name="niveau" required class="form-control">
                <option value="1">Niveau 1 - Débutant</option>
                <option value="2">Niveau 2 - Intermédiaire</option>
                <option value="3">Niveau 3 - Expérimenté</option>
            </select>
            <small class="form-text">Le niveau indique la complexité du poste</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Créer</button>
            <a href="<?= url('/admin/postes') ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../gabarits/pied_de_page.php'; ?>
