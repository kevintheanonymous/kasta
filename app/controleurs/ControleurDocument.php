<?php

require_once __DIR__ . '/../services/PDFService.php';

class ControleurDocument
{
    // telecharger formulaire adhesion en PDF
    public static function telechargerFormulaireAdhesion(): void
    {
        PDFService::genererFormulaireAdhesion();
    }

    // visualiser document adhesion (securise)
    public static function visualiserDocumentAdhesion(): void
    {
        // verifier que utilisateur est admin ou gestionnaire
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo 'Accès refusé';
            exit;
        }

        if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestionnaire')) {
            http_response_code(403);
            echo 'Accès refusé';
            exit;
        }

        // recuperer le chemin du fichier
        if (!isset($_GET['file'])) {
            http_response_code(400);
            echo 'Fichier non spécifié';
            exit;
        }

        $filePath = $_GET['file'];

        // securite : verifier que le chemin ne contient pas de ..
        if (strpos($filePath, '..') !== false) {
            http_response_code(400);
            echo 'Chemin invalide';
            exit;
        }

        // chemin complet
        $fullPath = __DIR__ . '/../../uploads/adhesions/' . basename($filePath);

        // verifier que fichier existe
        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo 'Fichier non trouvé';
            exit;
        }

        // determiner le type mime
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fullPath);

        // envoyer le fichier
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
