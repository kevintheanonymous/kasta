<?php
$pageTitle = "Modifier mon profil";
require_once __DIR__ . '/../gabarits/en_tete.php';
require_once __DIR__ . '/../gabarits/barre_nav.php';
?>
<link rel="stylesheet" href="<?= asset('css/profil_membre.css') ?>?v=<?= time() ?>">

<main>
    <div class="edit-profile-container">
        <div class="edit-profile-card">
            <a href="<?= url('/membre/profil') ?>" class="back-link">← Retour au profil</a>
            <h1>Modifier mon profil</h1>
            
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-error">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p style="margin: 0;"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <form action="<?= url('/membre/profil/update') ?>" method="post" enctype="multipart/form-data" class="edit-profile-form" id="editProfileForm">
                <?= champCSRF() ?>
                
                <div class="photo-section">
                    <?php $photoUrl = !empty($membre['Url_Photo_Profil']) ? asset($membre['Url_Photo_Profil']) : asset('img/avatar.jpg'); ?>
                    <img src="<?= $photoUrl ?>" alt="Photo de profil" id="photo-preview">
                    <label class="photo-upload-btn">
                        Changer la photo
                        <input type="file" name="photo" accept="image/*" id="photo-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" class="form-input" 
                           value="<?= htmlspecialchars($membre['Nom']) ?>" 
                           required minlength="2" maxlength="30"
                           pattern="^[A-Za-zÀ-ÿ\s'\-]+$"
                           title="Le nom doit contenir uniquement des lettres (2-30 caractères)">
                    <span class="field-error" id="nom-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom <span class="required">*</span></label>
                    <input type="text" id="prenom" name="prenom" class="form-input" 
                           value="<?= htmlspecialchars($membre['Prenom']) ?>" 
                           required minlength="2" maxlength="30"
                           pattern="^[A-Za-zÀ-ÿ\s'\-]+$"
                           title="Le prénom doit contenir uniquement des lettres (2-30 caractères)">
                    <span class="field-error" id="prenom-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?= htmlspecialchars($membre['Mail']) ?>" 
                           required>
                    <span class="field-error" id="email-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" class="form-input" 
                           value="<?= htmlspecialchars($membre['Telephone'] ?? '') ?>"
                           pattern="^0[1-9][0-9]{8}$"
                           placeholder="0612345678"
                           title="Format: 0612345678 (10 chiffres commençant par 0)">
                    <span class="field-error" id="telephone-error"></span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="taille_teeshirt">Taille T-shirt</label>
                        <select id="taille_teeshirt" name="taille_teeshirt" class="form-input">
                            <?php foreach(['XS','S','M','L','XL','XXL','3XL'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($membre['Taille_Teeshirt'] ?? '') == $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="taille_pull">Taille Pull</label>
                        <select id="taille_pull" name="taille_pull" class="form-input">
                            <?php foreach(['XS','S','M','L','XL','XXL','3XL'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($membre['Taille_Pull'] ?? '') == $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="regime_alimentaire">Régime alimentaire</label>
                    <select id="regime_alimentaire" name="regime_alimentaire" class="form-input">
                        <option value="">Aucun régime particulier</option>
                        <?php
                        if (!empty($regimesAlimentaires)) {
                            foreach ($regimesAlimentaires as $regime) {
                                $selected = ($membre['regime_alimentaire_id'] == $regime['id']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($regime['id']) . '" ' . $selected . '>' . htmlspecialchars($regime['nom']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="commentaires">Commentaires alimentaires (allergies, précisions...)</label>
                    <textarea id="commentaires" name="commentaires" class="form-input" rows="3" maxlength="500"><?= htmlspecialchars($membre['Commentaire_Alimentaire'] ?? '') ?></textarea>
                    <small class="field-hint">Maximum 500 caractères</small>
                </div>

                <button type="submit" class="btn-submit">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editProfileForm');
    const photoInput = document.getElementById('photo-input');
    const photoPreview = document.getElementById('photo-preview');
    
    // Preview photo on selection
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Real-time validation
    const validateField = (field, errorSpan) => {
        let message = '';
        
        if (field.validity.valueMissing) {
            message = 'Ce champ est obligatoire.';
        } else if (field.validity.tooShort) {
            message = `Minimum ${field.minLength} caractères requis.`;
        } else if (field.validity.tooLong) {
            message = `Maximum ${field.maxLength} caractères autorisés.`;
        } else if (field.validity.patternMismatch) {
            message = field.title || 'Format invalide.';
        } else if (field.validity.typeMismatch) {
            message = 'Format invalide.';
        }
        
        if (errorSpan) {
            errorSpan.textContent = message;
        }
        
        if (message) {
            field.classList.add('invalid');
            field.classList.remove('valid');
        } else if (field.value) {
            field.classList.remove('invalid');
            field.classList.add('valid');
        }
        
        return message === '';
    };
    
    // Add validation to each field
    ['nom', 'prenom', 'email', 'telephone'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const errorSpan = document.getElementById(fieldId + '-error');
        
        if (field) {
            field.addEventListener('blur', () => validateField(field, errorSpan));
            field.addEventListener('input', () => {
                if (field.classList.contains('invalid')) {
                    validateField(field, errorSpan);
                }
            });
        }
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        ['nom', 'prenom', 'email', 'telephone'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            const errorSpan = document.getElementById(fieldId + '-error');
            if (field && !validateField(field, errorSpan)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../gabarits/pied_de_page.php'; ?>