<?php

// Méthodes de validation partagées entre InscriptionValidator et ProfilValidator
trait ValidateurTrait
{
    private static function validerNomPrenom(string $valeur, string $libelle, array &$errors): void
    {
        if (empty($valeur)) {
            return;
        }
        if (strlen($valeur) < 2) {
            $errors[] = "Le $libelle doit contenir au moins 2 caractères.";
        }
        if (strlen($valeur) > 30) {
            $errors[] = "Le $libelle ne doit pas dépasser 30 caractères.";
        }
        if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/', $valeur)) {
            $errors[] = "Le $libelle contient des caractères invalides.";
        }
    }

    private static function validerTelephone(string $telephone, array &$errors): void
    {
        if (empty($telephone)) {
            return;
        }
        $tel = preg_replace('/\s+/', '', $telephone);
        if (!preg_match('/^0[1-9][0-9]{8}$/', $tel)) {
            $errors[] = "Le numéro de téléphone est invalide (format: 0612345678).";
        }
    }
}
