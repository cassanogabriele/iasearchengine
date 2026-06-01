<?php
require_once 'fonctions.php';
header('Content-Type: application/json'); // Force le navigateur à traiter la réponse comme du JSON

$id = $_POST['id'] ?? null;
if ($id && desarchiverRecherche($id)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors du désarchivage']);
}
exit; // Important pour arrêter toute exécution supplémentaire
?>