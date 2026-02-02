<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Durée maximale de session avant expiration (1 heure en secondes)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
}

// Détection automatique de l'URL racine du projet
if (!function_exists('get_base_url')) {
    function get_base_url(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        // Normalisation des séparateurs de chemin (conversion \ → /)
        $scriptDir = str_replace('\\', '/', $scriptDir);
        return rtrim($protocol . $host . $scriptDir, '/') . '/';
    }
}

// Vérifie si la session a expiré et déconnecte l'utilisateur si nécessaire
if (!function_exists('verifierTimeoutSession')) {
    function verifierTimeoutSession(): void
    {
        // Vérifier si l'utilisateur est connecté
        if (isset($_SESSION['user_id'])) {
                // on regarde si y'a un timestamp de derniere activité
            if (isset($_SESSION['derniere_activite'])) {
                $tempsInactif = time() - $_SESSION['derniere_activite'];

                // si ca fait plus d'1h qu'il a rien fait
                if ($tempsInactif > SESSION_TIMEOUT) {
                    // on detruit la session
                    session_unset();
                    session_destroy();

                    // on redemarre une session pour afficher le msg d'erreur
                    session_start();
                    $_SESSION['errors'] = ["Votre session a expiré après 1 heure d'inactivité. Veuillez vous reconnecter."];

                    // redirige vers la connexion
                    header('Location: ' . get_base_url() . 'index.php?path=/connexion');
                    exit();
                }
            }

            // on met a jour le timestamp (pour pas qu'il se fasse deconnecter)
            $_SESSION['derniere_activite'] = time();
        }
    }
}

// on verifie le timeout a chaque fois que la page charge
verifierTimeoutSession();

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = get_base_url();
        // on enleve le / au debut pour eviter d'avoir // dans l'url
        $path = ltrim($path, '/');

        // si y'a des params dans l'url on les separe
        if (strpos($path, '?') !== false) {
            list($route, $params) = explode('?', $path, 2);
            return $base . 'index.php?path=/' . $route . '&' . $params;
        }

        return $base . 'index.php?path=/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $base = get_base_url();
        $path = ltrim($path, '/');
        return $base . $path;
    }
}

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
        header('Location: ' . url($path));
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
if (!function_exists('empecherMiseEnCache')) {
    function empecherMiseEnCache(): void {
        // dit au navigateur de pas mettre en cache (sinon y'a des bugs bizarres)
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}

// donne la couleur du badge selon le statut du membre
// vert = valide, orange = en attente, rouge = refusé
if (!function_exists('getBadgeClass')) {
    function getBadgeClass(string $statut): string
    {
        return match($statut) {
            'valide' => 'success',
            'en_attente' => 'warning',
            'refuse' => 'danger',
            default => 'secondary'
        };
    }
}

// retourne si c'est un gestionaire ou un membre normal
if (!function_exists('getLibelleRole')) {
    function getLibelleRole(bool|int $estGestionnaire): string
    {
        return $estGestionnaire ? 'Gestionnaire' : 'Membre';
    }
}

// met le numero de tel au bon format (avec les espaces)
// ex: 0612345678 -> 06 12 34 56 78
if (!function_exists('formaterTelephone')) {
    function formaterTelephone(?string $telephone): string
    {
        if (empty($telephone)) {
            return '';
        }

        // regex pour ajouter les espaces tous les 2 chiffres
        return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $telephone);
    }
}


// PROTECTION CSRF (Cross-Site Request Forgery)


// Génère un token CSRF unique pour la session utilisateur
if (!function_exists('genererTokenCSRF')) {
    function genererTokenCSRF(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// verifie si le token csrf est bon
// si c'est pas le bon ca veut dire que c'est une attaque
if (!function_exists('verifierTokenCSRF')) {
    function verifierTokenCSRF(?string $token = null): bool
    {
        // si on a pas donné de token on le prend dans le POST
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }
        
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

// cree un input hidden avec le token dedans
// faut le mettre dans tout les formulaires
if (!function_exists('champCSRF')) {
    function champCSRF(): string
    {
        $token = genererTokenCSRF();
        return '<input type="hidden" name="csrf_token" value="' . nettoyer($token) . '">';
    }
}

// verifie le csrf et redirige si c'est pas bon
// plus pratique que de faire le check a chaque fois
if (!function_exists('validerCSRFOuRediriger')) {
    function validerCSRFOuRediriger(string $redirectPath = '/connexion'): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifierTokenCSRF($token)) {
            $_SESSION['errors'] = ['Session expirée ou requête invalide. Veuillez réessayer.'];
            rediriger($redirectPath);
        }
    }
}


// FONCTIONS POUR LES ADMINS ET GESTIONNAIRES


// regarde si l'user est admin ou gestionaire
if (!function_exists('estAdminOuGestionnaire')) {
    function estAdminOuGestionnaire(): bool
    {
        $userType = $_SESSION['user_type'] ?? '';
        return $userType === 'admin' || $userType === 'gestionnaire';
    }
}

// verifie les droits et redirige si l'user a pas le droit d'etre la
if (!function_exists('verifierAccesAdminOuGestionnaire')) {
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
}