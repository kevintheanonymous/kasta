<?php

require_once __DIR__ . '/../models/Membre.php';

// validateur pour le formulaire d'inscription
// verifie que tout les champs sont corrects avant d'inserer en bdd
class InscriptionValidator
{
    // valide les donnees du formulaire
    // retourne valid = true si tout est bon, sinon les erreurs
    public static function valider(array $data): array
    {
        $errors = [];

        // on check les champs obligatoires (commentaires est optionnel selon le schéma SQL)
        $champsObligatoires = ['nom', 'prenom', 'email', 'mdp', 'confmdp', 'sexe'];
        foreach ($champsObligatoires as $champ) {
            if (empty($data[$champ])) {
                $errors[] = "Le champ " . ucfirst($champ) . " est obligatoire.";
            }
        }

        // validation du nom (2-30 caracteres, que des lettres)
        if (!empty($data['nom'])) {
            if (strlen($data['nom']) < 2) {
                $errors[] = "Le nom doit contenir au moins 2 caractères.";
            }
            if (strlen($data['nom']) > 30) {
                $errors[] = "Le nom ne doit pas dépasser 30 caractères.";
            }
            if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/', $data['nom'])) {
                $errors[] = "Le nom contient des caractères invalides.";
            }
        }

        // pareil pour le prenom
        if (!empty($data['prenom'])) {
            if (strlen($data['prenom']) < 2) {
                $errors[] = "Le prénom doit contenir au moins 2 caractères.";
            }
            if (strlen($data['prenom']) > 30) {
                $errors[] = "Le prénom ne doit pas dépasser 30 caractères.";
            }
            if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/', $data['prenom'])) {
                $errors[] = "Le prénom contient des caractères invalides.";
            }
        }

        // validation de l'email
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse email est invalide.";
            } else {
                // on regarde si l'email est deja pris
                if (Membre::emailExiste($data['email'])) {
                    $errors[] = "Cet email est déjà utilisé.";
                }
            }
        }

        // validation du telephone (format francais)
        if (!empty($data['telephone'])) {
            if (!preg_match('/^0[1-9][0-9]{8}$/', $data['telephone'])) {
                $errors[] = "Le numéro de téléphone est invalide (format: 0612345678).";
            }
        }

        // validation du mot de passe (faut tout ca pour la securité)
        if (!empty($data['mdp'])) {
            if (strlen($data['mdp']) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }
            if (!preg_match('/[a-z]/', $data['mdp'])) {
                $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
            }
            if (!preg_match('/[A-Z]/', $data['mdp'])) {
                $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule.";
            }
            if (!preg_match('/[0-9]/', $data['mdp'])) {
                $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
            }
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $data['mdp'])) {
                $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
            }
        }

        // check si les 2 mdp sont identiques
        if (!empty($data['mdp']) && !empty($data['confmdp'])) {
            if ($data['mdp'] !== $data['confmdp']) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }
        }

        // le sexe doit etre H ou F
        if (!empty($data['sexe']) && !in_array($data['sexe'], ['H', 'F'])) {
            $errors[] = "Le sexe doit être H ou F.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
