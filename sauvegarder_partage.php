<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';


// Forcer le type de contenu en JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['html_content'])) {
    try {
        $token = bin2hex(random_bytes(16));
        $html = $_POST['html_content'];

        $stmt = $pdo->prepare("INSERT INTO analyses_partagees (token, contenu_html) VALUES (?, ?)");
        $stmt->execute([$token, $html]);

        echo json_encode(['status' => 'success', 'url' => 'https://iasearchengine.gabriel-cassano.be/index.php?token=' . $token]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Requête invalide']);
}
exit;
?>