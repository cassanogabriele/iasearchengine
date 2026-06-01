<?php
require_once 'config.php';
header('Content-Type: application/json'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        $stmt = $pdo->prepare("UPDATE fiches_produits SET archive = 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erreur SQL']);
    }
    exit;
}
?>