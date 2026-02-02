<?php

require_once __DIR__ . '/../models/Membre.php';

/**
 * Validateur pour la modification de profil
 * Vérifie que tous les champs sont corrects avant la mise à jour
 */
class ProfilValidator
{
    /**
     * Valide les données du formulaire de modification de profil
     * @param array $data Les données du formulaire
     * @param int $userId L'ID de l'utilisateur (pour vérifier l'email unique)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function valider(array $data, int $userId): array
    {
        $errors = [];

        // Champs obligatoires
        $champsObligatoires = ['nom', 'prenom', 'email'];
        foreach ($champsObligatoires as $champ) {
            if (empty($data[$champ])) {
                $errors[] = "Le champ " . ucfirst($champ) . " est obligatoire.";
            }
        }

        // Validation du nom (2-30 caractères, lettres uniquement)
        if (!empty($data['nom'])) {
            $nom = trim($data['nom']);
            if (strlen($nom) < 2) {
                $errors[] = "Le nom doit contenir au moins 2 caractères.";
            }
            if (strlen($nom) > 30) {
                $errors[] = "Le nom ne doit pas dépasser 30 caractères.";
            }
            if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/', $nom)) {
                $errors[] = "Le nom contient des caractères invalides.";
            }
        }

        // Validation du prénom (2-30 caractères, lettres uniquement)
        if (!empty($data['prenom'])) {
            $prenom = trim($data['prenom']);
            if (strlen($prenom) < 2) {
                $errors[] = "Le prénom doit contenir au moins 2 caractères.";
            }
            if (strlen($prenom) > 30) {
                $errors[] = "Le prénom ne doit pas dépasser 30 caractères.";
            }
            if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/', $prenom)) {
                $errors[] = "Le prénom contient des caractères invalides.";
            }
        }

        // Validation de l'email
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse email est invalide.";
            } else {
                // Vérifier si l'email est déjà pris par un autre utilisateur
                if (Membre::emailExisteAutreUtilisateur($data['email'], $userId)) {
                    $errors[] = "Cet email est déjà utilisé par un autre compte.";
                }
            }
        }

        // Validation du téléphone (optionnel mais doit être valide si renseigné)
        if (!empty($data['telephone'])) {
            $tel = preg_replace('/\s+/', '', $data['telephone']); // Supprimer les espaces
            if (!preg_match('/^0[1-9][0-9]{8}$/', $tel)) {
                $errors[] = "Le numéro de téléphone est invalide (format: 0612345678).";
            }
        }

        // Validation des tailles (doivent être dans la liste autorisée)
        $taillesAutorisees = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', ''];
        
        if (!empty($data['taille_teeshirt']) && !in_array($data['taille_teeshirt'], $taillesAutorisees)) {
            $errors[] = "La taille de t-shirt sélectionnée est invalide.";
        }
        
        if (!empty($data['taille_pull']) && !in_array($data['taille_pull'], $taillesAutorisees)) {
            $errors[] = "La taille de pull sélectionnée est invalide.";
        }

        // Validation des commentaires (max 500 caractères)
        if (!empty($data['commentaires']) && strlen($data['commentaires']) > 500) {
            $errors[] = "Les commentaires ne doivent pas dépasser 500 caractères.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
