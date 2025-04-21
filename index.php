<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8100'); // Autoriser Ionic
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Connexion à la base de données
$host = 'localhost';
$user = 'root'; // Utilisateur MySQL par défaut sur XAMPP
$password = ''; // Mot de passe vide par défaut sur XAMPP
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
    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);

    // Valider les données
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

    // Enregistrer dans la base de données
    $stmt = $pdo->prepare('INSERT INTO form_submissions (name, email, message) VALUES (?, ?, ?)');
    $stmt->execute([$input['name'], $input['email'], $input['message']]);

    echo json_encode(['message' => 'Formulaire soumis avec succès !']);
} elseif ($method === 'GET') {
    // Récupérer toutes les soumissions
    $stmt = $pdo->query('SELECT name, email, message FROM form_submissions');
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($submissions);
} else {
    echo json_encode(['error' => 'Méthode non autorisée']);
}