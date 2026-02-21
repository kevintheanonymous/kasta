<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SESSION_TIMEOUT', 3600);

function get_base_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    return rtrim($protocol . $host . $scriptDir, '/') . '/';
}

function verifierTimeoutSession(): void
{
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['derniere_activite'])) {
            $tempsInactif = time() - $_SESSION['derniere_activite'];
            if ($tempsInactif > SESSION_TIMEOUT) {
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['errors'] = ["Votre session a expiré après 1 heure d'inactivité. Veuillez vous reconnecter."];
                header('Location: ' . get_base_url() . 'index.php?path=/connexion');
                exit();
            }
        }
        $_SESSION['derniere_activite'] = time();
    }
}

verifierTimeoutSession();

function url(string $path = ''): string {
    $base = get_base_url();
    $path = ltrim($path, '/');
    if (strpos($path, '?') !== false) {
        list($route, $params) = explode('?', $path, 2);
        return $base . 'index.php?path=/' . $route . '&' . $params;
    }
    return $base . 'index.php?path=/' . $path;
}

function asset(string $path): string {
    $base = get_base_url();
    $path = ltrim($path, '/');
    return $base . $path;
}

function estConnecte(): bool
{
    return isset($_SESSION['user_id']);
}

function estAdmin(): bool
{
    return ($_SESSION['user_type'] ?? '') === 'admin';
}

function rediriger(string $path): void
{
    header('Location: ' . url($path));
    exit();
}

function nettoyer(?string $valeur): string
{
    return htmlspecialchars((string)($valeur ?? ''), ENT_QUOTES, 'UTF-8');
}

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

function empecherMiseEnCache(): void {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
}

function getBadgeClass(string $statut): string
{
    return match($statut) {
        'valide' => 'success',
        'en_attente' => 'warning',
        'refuse' => 'danger',
        default => 'secondary'
    };
}

function getLibelleRole(bool|int $estGestionnaire): string
{
    return $estGestionnaire ? 'Gestionnaire' : 'Membre';
}

function formaterTelephone(?string $telephone): string
{
    if (empty($telephone)) {
        return '';
    }
    return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $telephone);
}


// PROTECTION CSRF avec rotation des tokens

function genererTokenCSRF(): string
{
    // Generer un nouveau token a chaque appel de formulaire
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verifierTokenCSRF(?string $token = null): bool
{
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    $valide = hash_equals($_SESSION['csrf_token'], $token);

    // Rotation : invalider le token apres verification reussie
    if ($valide) {
        unset($_SESSION['csrf_token']);
    }

    return $valide;
}

function champCSRF(): string
{
    $token = genererTokenCSRF();
    return '<input type="hidden" name="csrf_token" value="' . nettoyer($token) . '">';
}

function validerCSRFOuRediriger(string $redirectPath = '/connexion'): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!verifierTokenCSRF($token)) {
        $_SESSION['errors'] = ['Session expirée ou requête invalide. Veuillez réessayer.'];
        rediriger($redirectPath);
    }
}

// Validation de redirection interne (protection open redirect)
function validerUrlRetour(string $urlRetour, string $defaut = '/admin/events'): string
{
    // N'autoriser que les chemins internes commencant par /
    $urlRetour = trim($urlRetour);
    if (empty($urlRetour) || !preg_match('#^/[a-zA-Z0-9_/&=?.-]*$#', $urlRetour)) {
        return $defaut;
    }
    return $urlRetour;
}


// FONCTIONS POUR LES ADMINS ET GESTIONNAIRES

function estAdminOuGestionnaire(): bool
{
    $userType = $_SESSION['user_type'] ?? '';
    return $userType === 'admin' || $userType === 'gestionnaire';
}

function verifierAccesAdminOuGestionnaire(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    empecherMiseEnCache();

    if (!estAdminOuGestionnaire()) {
        $_SESSION['errors'] = ['Accès réservé aux administrateurs et gestionnaires.'];
        rediriger('/connexion');
    }
}