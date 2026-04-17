<?php
// Sécurité : exécution CLI uniquement
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit(1);
}

require_once __DIR__ . '/../config/env.php';
Env::load();

// ─── Connexion BDD ────────────────────────────────────────────────────────────

function connecterBDD(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        Env::get('DB_HOST', 'localhost'),
        Env::get('DB_PORT', '3306'),
        Env::get('DB_NAME', 'kastasso'),
        Env::get('DB_CHARSET', 'utf8mb4')
    );

    try {
        $pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASSWORD', ''));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        sortirErreur('Connexion à la base de données impossible : ' . $e->getMessage());
    }
}

// ─── I/O ──────────────────────────────────────────────────────────────────────

function lire(string $invite): string
{
    echo $invite;
    return trim(fgets(STDIN));
}

function lireMotDePasse(string $invite): string
{
    echo $invite;
    system('stty -echo');
    $valeur = trim(fgets(STDIN));
    system('stty echo');
    echo PHP_EOL;
    return $valeur;
}

function sortirErreur(string $message): never
{
    echo "\033[31m[ERREUR] $message\033[0m" . PHP_EOL;
    exit(1);
}

function afficherSucces(string $message): void
{
    echo "\033[32m[OK] $message\033[0m" . PHP_EOL;
}

function afficherInfo(string $message): void
{
    echo "\033[33m[INFO] $message\033[0m" . PHP_EOL;
}

// ─── Validation ───────────────────────────────────────────────────────────────

function validerMotDePasse(string $mdp): array
{
    $erreurs = [];
    if (strlen($mdp) < 8)                    $erreurs[] = 'Au moins 8 caractères.';
    if (!preg_match('/[A-Z]/', $mdp))         $erreurs[] = 'Au moins une majuscule.';
    if (!preg_match('/[a-z]/', $mdp))         $erreurs[] = 'Au moins une minuscule.';
    if (!preg_match('/\d/', $mdp))            $erreurs[] = 'Au moins un chiffre.';
    if (!preg_match('/[^A-Za-z0-9]/', $mdp)) $erreurs[] = 'Au moins un caractère spécial.';
    return $erreurs;
}

// ─── Programme principal ──────────────────────────────────────────────────────

echo PHP_EOL;
echo '╔══════════════════════════════════════════╗' . PHP_EOL;
echo '║     Création du compte administrateur    ║' . PHP_EOL;
echo '╚══════════════════════════════════════════╝' . PHP_EOL;
echo PHP_EOL;

$pdo = connecterBDD();

// Vérification : admin déjà existant
$stmt = $pdo->query('SELECT COUNT(*) FROM admin');
if ((int) $stmt->fetchColumn() > 0) {
    afficherInfo('Un compte administrateur existe déjà en base de données.');
    afficherInfo('Pour réinitialiser le mot de passe, utilisez la page « Mot de passe oublié » de l\'application.');
    echo PHP_EOL;
    exit(0);
}

afficherInfo('Aucun compte administrateur trouvé. Veuillez en créer un.');
echo PHP_EOL;

// Saisie de l'identifiant
while (true) {
    $identifiant = lire('Identifiant : ');
    if (strlen($identifiant) >= 3 && strlen($identifiant) <= 100) break;
    echo '   L\'identifiant doit contenir entre 3 et 100 caractères.' . PHP_EOL;
}

// Saisie de l'email
while (true) {
    $email = strtolower(trim(lire('Email : ')));
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 255) break;
    echo '   Adresse email invalide.' . PHP_EOL;
}

// Saisie et confirmation du mot de passe
while (true) {
    $mdp = lireMotDePasse('Mot de passe : ');
    $erreurs = validerMotDePasse($mdp);
    if (!empty($erreurs)) {
        echo '   Mot de passe invalide :' . PHP_EOL;
        foreach ($erreurs as $e) echo "     - $e" . PHP_EOL;
        continue;
    }

    $confirmation = lireMotDePasse('Confirmer le mot de passe : ');
    if (!hash_equals($mdp, $confirmation)) {
        echo '   Les mots de passe ne correspondent pas.' . PHP_EOL;
        continue;
    }

    break;
}

// Insertion en base (requête préparée, protection injection SQL)
try {
    $stmt = $pdo->prepare(
        'INSERT INTO admin (identifiant, Mail, Mot_de_passe) VALUES (:identifiant, :email, :mdp)'
    );
    $stmt->execute([
        ':identifiant' => $identifiant,
        ':email'       => $email,
        ':mdp'         => password_hash($mdp, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $e) {
    sortirErreur('Échec de la création du compte : ' . $e->getMessage());
}

echo PHP_EOL;
afficherSucces('Compte administrateur créé avec succès.');
echo PHP_EOL;
