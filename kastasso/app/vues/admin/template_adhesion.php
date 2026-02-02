<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Adhésion - Admin</title>
    <link rel="stylesheet" href="<?= asset('css/variables.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/gabarit.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset('css/admin_crud.css?v=' . time()) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .template-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .template-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .template-section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
        }

        .current-template {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: linear-gradient(135deg, #ecf0f1 0%, #f8f9fa 100%);
            border-radius: 10px;
            border: 2px solid #3498db;
        }

        .current-template.custom {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border-color: #4caf50;
        }

        .template-info {
            flex: 1;
        }

        .template-info h3 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .template-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .template-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-preview,
        .btn-delete-template {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            min-width: 140px;
            height: 48px;
            box-sizing: border-box;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-preview {
            background: var(--accent-color, #3498db);
        }

        .btn-preview:hover {
            background: var(--accent-dark, #2980b9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .btn-delete-template {
            background: var(--danger-color, #e74c3c);
        }

        .btn-delete-template:hover {
            background: var(--danger-dark, #c0392b);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .upload-section {
            margin-top: 20px;
        }

        .upload-zone {
            border: 3px dashed #ccc;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            background: #fafafa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-zone:hover, .upload-zone.dragover {
            border-color: #3498db;
            background: #ecf0f1;
        }

        .upload-zone .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .upload-zone h3 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .upload-zone p {
            margin: 0;
            color: #666;
        }

        .upload-zone input[type="file"] {
            display: none;
        }

        .file-info {
            margin-top: 15px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 8px;
            display: none;
        }

        .file-info.show {
            display: block;
        }

        .file-info .file-name {
            font-weight: 600;
            color: #333;
        }

        .btn-upload {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #4caf50 0%, #43a047 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
        }

        .btn-upload:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .info-box {
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 0 8px 8px 0;
            margin-bottom: 20px;
        }

        .info-box p {
            margin: 0;
            color: #856404;
        }

        .preview-frame {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 15px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .back-link:hover {
            color: #3498db;
        }

        /* ============================================
           RESPONSIVE STYLES
           ============================================ */
        @media (max-width: 768px) {
            .template-container {
                padding: 10px;
            }

            .template-section {
                padding: 15px;
                margin-bottom: 15px;
            }

            .template-section h2 {
                font-size: 1.2rem;
                margin-bottom: 15px;
            }

            /* Stack the current-template content vertically */
            .current-template {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                padding: 15px;
            }

            .template-info {
                width: 100%;
            }

            .template-info h3 {
                font-size: 1.1rem;
            }

            .template-info p {
                font-size: 0.85rem;
                word-break: break-word;
            }

            /* Stack buttons vertically */
            .template-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }

            .template-actions .btn-preview,
            .template-actions .btn-delete-template {
                width: 100%;
                justify-content: center;
                min-height: 44px;
                padding: 12px 20px;
                box-sizing: border-box;
            }

            .template-actions form {
                width: 100%;
            }

            .template-actions form button {
                width: 100%;
            }

            /* Upload zone */
            .upload-zone {
                padding: 25px 15px;
            }

            .upload-zone .icon {
                font-size: 2rem;
            }

            .upload-zone h3 {
                font-size: 1rem;
            }

            .btn-upload {
                width: 100%;
                justify-content: center;
                min-height: 48px;
            }

            /* Preview frame */
            .preview-frame {
                height: 400px;
            }

            /* Page title */
            h1 {
                font-size: 1.5rem;
                line-height: 1.3;
            }
        }

        @media (max-width: 480px) {
            .template-container {
                padding: 5px;
            }

            .template-section {
                padding: 12px;
                border-radius: 8px;
            }

            .current-template {
                padding: 12px;
                border-radius: 8px;
            }

            .preview-frame {
                height: 300px;
            }

            h1 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../gabarits/barre_nav.php'; ?>

    <main>
        <div class="template-container">
            <a href="<?= url('/admin/dashboard') ?>" class="back-link">
                ← Retour au tableau de bord
            </a>

            <h1>Gestion du Template d'Adhésion</h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['errors'])): ?>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <div class="alert alert-danger">
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <!-- Section Template Actuel -->
            <div class="template-section">
                <h2>Template Actuel</h2>
                
                <div class="current-template <?= $hasCustomTemplate ? 'custom' : '' ?>">
                    <div class="template-info">
                        <?php if ($hasCustomTemplate): ?>
                            <h3>Template Personnalisé</h3>
                            <p>Un fichier PDF personnalisé est utilisé pour les formulaires d'adhésion.</p>
                            <p><small>Fichier : <?= htmlspecialchars($customTemplateName) ?></small></p>
                        <?php else: ?>
                            <h3>Template par Défaut</h3>
                            <p>Le formulaire d'adhésion est généré automatiquement par le système.</p>
                        <?php endif; ?>
                    </div>
                    <div class="template-actions">
                        <a href="<?= url('/admin/template-adhesion/preview') ?>" class="btn-preview" target="_blank">
                            Visualiser
                        </a>
                        <?php if ($hasCustomTemplate): ?>
                            <form method="post" action="<?= url('/admin/template-adhesion/delete') ?>" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le template personnalisé ? Le template par défaut sera utilisé.')">
                                <?= champCSRF() ?>
                                <button type="submit" class="btn-delete-template">
                                    Supprimer
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section Upload -->
            <div class="template-section">
                <h2>Ajouter un Template Personnalisé</h2>
                
                <div class="info-box">
                    <p><strong>Information :</strong> Vous pouvez téléverser un fichier PDF vierge qui sera utilisé comme formulaire d'adhésion. Les membres pourront le télécharger, l'imprimer et le remplir manuellement.</p>
                </div>

                <form method="post" action="<?= url('/admin/template-adhesion/upload') ?>" enctype="multipart/form-data" id="uploadForm">
                    <?= champCSRF() ?>
                    
                    <div class="upload-zone" id="uploadZone">
                        <div class="icon">PDF</div>
                        <h3>Glissez-déposez votre fichier PDF ici</h3>
                        <p>ou cliquez pour sélectionner un fichier</p>
                        <p><small>Format accepté : PDF uniquement (max 5 Mo)</small></p>
                        <input type="file" name="template_pdf" id="templateFile" accept=".pdf,application/pdf">
                    </div>

                    <div class="file-info" id="fileInfo">
                        <span class="file-name" id="fileName"></span>
                    </div>

                    <button type="submit" class="btn-upload" id="btnUpload" disabled>
                        Téléverser le template
                    </button>
                </form>
            </div>

            <!-- Section Prévisualisation -->
            <?php if ($hasCustomTemplate): ?>
            <div class="template-section">
                <h2>Aperçu du Template Personnalisé</h2>
                <iframe src="<?= url('/admin/template-adhesion/preview') ?>" class="preview-frame"></iframe>
            </div>
            <?php endif; ?>

        </div>
    </main>

    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('templateFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const btnUpload = document.getElementById('btnUpload');

        // Click to upload
        uploadZone.addEventListener('click', () => fileInput.click());

        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                fileInput.files = files;
                updateFileInfo(files[0]);
            } else {
                alert('Veuillez sélectionner un fichier PDF valide.');
            }
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateFileInfo(e.target.files[0]);
            }
        });

        function updateFileInfo(file) {
            if (file.type !== 'application/pdf') {
                alert('Veuillez sélectionner un fichier PDF valide.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux (max 5 Mo).');
                return;
            }

            fileName.textContent = '📄 ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' Mo)';
            fileInfo.classList.add('show');
            btnUpload.disabled = false;
        }
    </script>

    <?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>
</body>
</html>
