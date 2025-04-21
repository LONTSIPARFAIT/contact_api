<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8100');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Connexion à la base de données
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'contact_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connexion échouée : ' . $e->getMessage()]);
    exit;
}

// Gérer les requêtes
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Créer une soumission
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name']) || strlen($input['name']) < 3) {
        echo json_encode(['error' => 'Le nom est requis (minimum 3 caractères)']);
        exit;
    }
    if (!isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Veuillez entrer un email valide']);
        exit;
    }
    if (!isset($input['message']) || empty($input['message'])) {
        echo json_encode(['error' => 'Le message est requis']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO form_submissions (name, email, message) VALUES (?, ?, ?)');
    $stmt->execute([$input['name'], $input['email'], $input['message']]);

    echo json_encode(['message' => 'Formulaire soumis avec succès !']);
} elseif ($method === 'GET') {
    // Récupérer toutes les soumissions avec l’id
    $stmt = $pdo->query('SELECT id, name, email, message FROM form_submissions');
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($submissions);
} elseif ($method === 'PUT') {
    // Modifier une soumission
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || !is_numeric($input['id'])) {
        echo json_encode(['error' => 'ID invalide']);
        exit;
    }
    if (!isset($input['name']) || strlen($input['name']) < 3) {
        echo json_encode(['error' => 'Le nom est requis (minimum 3 caractères)']);
        exit;
    }
    if (!isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Veuillez entrer un email valide']);
        exit;
    }
    if (!isset($input['message']) || empty($input['message'])) {
        echo json_encode(['error' => 'Le message est requis']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE form_submissions SET name = ?, email = ?, message = ? WHERE id = ?');
    $stmt->execute([$input['name'], $input['email'], $input['message'], $input['id']]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'Aucune soumission trouvée avec cet ID']);
        exit;
    }

    echo json_encode(['message' => 'Soumission modifiée avec succès !']);
} elseif ($method === 'DELETE') {
    // Supprimer une soumission
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || !is_numeric($input['id'])) {
        echo json_encode(['error' => 'ID invalide']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM form_submissions WHERE id = ?');
    $stmt->execute([$input['id']]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'Aucune soumission trouvée avec cet ID']);
        exit;
    }

    echo json_encode(['message' => 'Soumission supprimée avec succès !']);
} else {
    echo json_encode(['error' => 'Méthode non autorisée']);
}