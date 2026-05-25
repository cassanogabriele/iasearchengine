<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        // Met à jour la bdd pour marquer la recherche comme archivée
        $stmt = $pdo->prepare("UPDATE fiches_produits SET archive = 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "OK";
    } catch (Exception $e) {
        http_response_code(500);
        echo "Erreur SQL";
    }
    exit;
}
?>