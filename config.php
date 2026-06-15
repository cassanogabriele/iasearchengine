<?php
// Configuration de la base de données 
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'iasearchengine');
define('DB_USER', 'giasearchengine');
define('DB_PASS', 'Ib2SwjmiZ7Kh0rJ3XNsh');

// Configuration de l'API Ollama (Local)
define('API_URL', 'http://127.0.0.1:11434/api/generate');
// Version plus rapide de l'IA
define('AI_MODEL', 'llama3.2:1b');

try {
    // Passage en utf8mb4 pour supporter tous les caractères générés par l'IA
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Paramètres d'affichage
$site_name = "Mon Assistant IA";
?>