<?php

require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/ValidateurTrait.php';

/**
 * Validateur pour la modification de profil
 * Vérifie que tous les champs sont corrects avant la mise à jour
 */
class ProfilValidator
{
    use ValidateurTrait;
    /**
     * Valide les données du formulaire de modification de profil
     * @param array $data Les données du formulaire
     * @param int $userId L'ID de l'utilisateur (pour vérifier l'email unique)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function valider(array $data, int $userId): array
    {
        $errors = [];

        $champsObligatoires = ['nom', 'prenom', 'email'];
        foreach ($champsObligatoires as $champ) {
            if (empty($data[$champ])) {
                $errors[] = "Le champ " . ucfirst($champ) . " est obligatoire.";
            }
        }

        self::validerNomPrenom(trim($data['nom'] ?? ''), 'nom', $errors);
        self::validerNomPrenom(trim($data['prenom'] ?? ''), 'prénom', $errors);
        self::validerEmail($data['email'] ?? '', $userId, $errors);
        self::validerTelephone($data['telephone'] ?? '', $errors);
        self::validerTailles($data, $errors);

        if (!empty($data['commentaires']) && strlen($data['commentaires']) > 500) {
            $errors[] = "Les commentaires ne doivent pas dépasser 500 caractères.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private static function validerEmail(string $email, int $userId, array &$errors): void
    {
        if (empty($email)) {
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email est invalide.";
        } elseif (Membre::emailExisteAutreUtilisateur($email, $userId)) {
            $errors[] = "Cet email est déjà utilisé par un autre compte.";
        }
    }

    private static function validerTailles(array $data, array &$errors): void
    {
        $taillesAutorisees = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', ''];
        if (!empty($data['taille_teeshirt']) && !in_array($data['taille_teeshirt'], $taillesAutorisees)) {
            $errors[] = "La taille de t-shirt sélectionnée est invalide.";
        }
        if (!empty($data['taille_pull']) && !in_array($data['taille_pull'], $taillesAutorisees)) {
            $errors[] = "La taille de pull sélectionnée est invalide.";
        }
    }
}
