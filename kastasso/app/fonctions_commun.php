<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/models/BaseDeDonnees.php';

const BASE_ROUTE_PREFIX = '/kastasso/public/index.php?path=';

if (!function_exists('estConnecte')) {
    function estConnecte(): bool
    {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('estAdmin')) {
    function estAdmin(): bool
    {
        return ($_SESSION['user_type'] ?? '') === 'admin';
    }
}

if (!function_exists('rediriger')) {
    function rediriger(string $path): void
    {
        $normalized = '/' . ltrim($path, '/');
        header('Location: ' . BASE_ROUTE_PREFIX . $normalized);
        exit();
    }
}

if (!function_exists('nettoyer')) {
    function nettoyer(?string $valeur): string
    {
        return htmlspecialchars((string)($valeur ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formaterDateHeure')) {
    function formaterDateHeure(?string $datetime): string
    {
        if (!$datetime) {
            return 'À définir';
        }
        try {
            return (new DateTime($datetime))->format('d/m/Y H:i');
        } catch (Throwable $e) {
            return $datetime;
        }
    }
}

if (!function_exists('obtenirEvenementsParCategorie')) {
    function obtenirEvenementsParCategorie(string $categorie): array
    {
        try {
            $pdo = BaseDeDonnees::getConnexion();
            $sql = "SELECT id_evenement, titre, description, date_evenement, lieu, categorie 
                    FROM evenement 
                    WHERE categorie = :categorie 
                    ORDER BY date_evenement ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':categorie' => $categorie]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('obtenirEvenementsParCategorie: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('obtenirTousEvenementsSport')) {
    function obtenirTousEvenementsSport(): array
    {
        return obtenirEvenementsParCategorie('sport');
    }
}

if (!function_exists('obtenirTousEvenementsAsso')) {
    function obtenirTousEvenementsAsso(): array
    {
        return obtenirEvenementsParCategorie('association');
    }
}?>