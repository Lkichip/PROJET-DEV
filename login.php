<?php
// CORS complet
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Forcer affichage des erreurs pour debug
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Connexion MySQL
$host = 'localhost';
$dbname = 'pepsidb';
$user = 'Pepsi';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'ERREUR PDO', 'message' => $e->getMessage()]);
    exit();
}

// Lecture du JSON envoyé
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'JSON invalide', 'input' => $input]);
    exit();
}

$email = $data['login'] ?? ''; // On reçoit encore "login" du frontend mais on suppose que c'est l'email
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Champs requis manquants', 'received' => $data]);
    exit();
}

// Requête SQL
try {
    $stmt = $pdo->prepare("SELECT id_utilisateur, nom, prenom, email, role FROM utilisateurs WHERE email = ? AND mot_de_passe = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'success' => true,
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'role' => $user['role']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Identifiants incorrects']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur SQL', 'message' => $e->getMessage()]);
}
?>
