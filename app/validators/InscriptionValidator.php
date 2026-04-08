<?php

require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/ValidateurTrait.php';

// validateur pour le formulaire d'inscription
// verifie que tout les champs sont corrects avant d'inserer en bdd
class InscriptionValidator
{
    use ValidateurTrait;
    // valide les donnees du formulaire
    // retourne valid = true si tout est bon, sinon les erreurs
    public static function valider(array $data): array
    {
        $errors = [];

        $champsObligatoires = ['nom', 'prenom', 'email', 'mdp', 'confmdp', 'sexe'];
        foreach ($champsObligatoires as $champ) {
            if (empty($data[$champ])) {
                $errors[] = "Le champ " . ucfirst($champ) . " est obligatoire.";
            }
        }

        self::validerNomPrenom($data['nom'] ?? '', 'nom', $errors);
        self::validerNomPrenom($data['prenom'] ?? '', 'prénom', $errors);
        self::validerEmail($data['email'] ?? '', $errors);
        self::validerTelephone($data['telephone'] ?? '', $errors);
        self::validerMotDePasse($data['mdp'] ?? '', $data['confmdp'] ?? '', $errors);

        if (!empty($data['sexe']) && !in_array($data['sexe'], ['H', 'F'])) {
            $errors[] = "Le sexe doit être H ou F.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private static function validerEmail(string $email, array &$errors): void
    {
        if (empty($email)) {
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email est invalide.";
        } elseif (Membre::emailExiste($email)) {
            $errors[] = "Cet email est déjà utilisé.";
        }
    }

    private static function validerMotDePasse(string $mdp, string $confmdp, array &$errors): void
    {
        if (empty($mdp)) {
            return;
        }
        if (strlen($mdp) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        if (!preg_match('/[a-z]/', $mdp)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
        }
        if (!preg_match('/[A-Z]/', $mdp)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule.";
        }
        if (!preg_match('/[0-9]/', $mdp)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $mdp)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
        }
        if (!empty($confmdp) && $mdp !== $confmdp) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
    }
}
