<?php

// service pour gerer les uploads de fichiers
// surtout les photos de profil pour l'instant
class FileUploadService
{
    private const UPLOAD_DIR = '/../../public/uploads/';
    private const ADHESION_DIR = '/../../uploads/adhesions/';
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5Mo max
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const ALLOWED_ADHESION_TYPES = ['application/pdf', 'image/jpeg'];
    private const ALLOWED_ADHESION_EXTENSIONS = ['pdf', 'jpg', 'jpeg'];

    // upload une photo de profil
    // retourne un tableau avec success, path et message
    public static function uploadPhoto(array $file): array
    {
        // check si y'a bien un fichier
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Aucun fichier uploadé ou erreur lors de l\'upload'
            ];
        }

        // check la taille
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Le fichier ne doit pas dépasser 5 Mo'
            ];
        }

        // check le type mime (ca regarde le VRAI contenu du fichier, pas juste l'extension)
        // comme ca meme si quelqu'un renomme virus.exe en photo.jpg, on detecte que c'est pas une image
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP'
            ];
        }

        // check l'extension aussi (double securité)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Extension de fichier non autorisée'
            ];
        }

        // on cree le dossier si il existe pas
        $uploadDir = __DIR__ . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return [
                    'success' => false,
                    'path' => null,
                    'message' => 'Impossible de créer le dossier d\'upload'
                ];
            }
        }

        // on genere un nom unique pour eviter les conflits
        $newFileName = uniqid('img_', true) . '.' . $ext;
        $destPath = $uploadDir . $newFileName;

        // on deplace le fichier
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Erreur lors du déplacement du fichier'
            ];
        }

        // on retourne le chemin relatif pour la bdd
        return [
            'success' => true,
            'path' => 'uploads/' . $newFileName,
            'message' => 'Fichier uploadé avec succès'
        ];
    }

    // supprime une photo de profil du serveur
    public static function deletePhoto(string $photoPath): bool
    {
        if (empty($photoPath)) {
            return false;
        }

        $fullPath = __DIR__ . '/../../public/' . $photoPath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    // upload formulaire adhesion (PDF ou image)
    public static function uploadFormulaireAdhesion(array $file, int $idMembre): array
    {
        // check fichier
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Aucun fichier uploadé ou erreur lors de l\'upload'
            ];
        }

        // check taille
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Le fichier ne doit pas dépasser 5 Mo'
            ];
        }

        // check type mime
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_ADHESION_TYPES)) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Type de fichier non autorisé. Utilisez PDF, JPG ou JPEG uniquement'
            ];
        }

        // check extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_ADHESION_EXTENSIONS)) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Extension de fichier non autorisée. Utilisez PDF, JPG ou JPEG'
            ];
        }

        // creer dossier si existe pas
        $uploadDir = __DIR__ . self::ADHESION_DIR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return [
                    'success' => false,
                    'path' => null,
                    'message' => 'Impossible de créer le dossier d\'upload'
                ];
            }
        }

        // nom unique avec id membre
        $newFileName = 'adhesion_' . $idMembre . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $newFileName;

        // deplacer fichier
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return [
                'success' => false,
                'path' => null,
                'message' => 'Erreur lors du déplacement du fichier'
            ];
        }

        // retourner chemin relatif
        return [
            'success' => true,
            'path' => 'uploads/adhesions/' . $newFileName,
            'message' => 'Formulaire d\'adhésion uploadé avec succès'
        ];
    }

    // supprimer formulaire adhesion
    public static function deleteFormulaireAdhesion(string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }

        $fullPath = __DIR__ . '/../../' . $filePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }
}
