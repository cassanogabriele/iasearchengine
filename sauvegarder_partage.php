<?php
// Forcer le type de contenu en JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['html_content'])) {
    try {
        $token = bin2hex(random_bytes(16));
        $html = $_POST['html_content'];

        // Définir le nombre max de partages que tu autorises
        $max_partages = 100; 

        // Compter les entrées actuelles
        $count = $pdo->query("SELECT COUNT(*) FROM analyses_partagees")->fetchColumn();

        // Si on dépasse, supprimer le plus vieux
        if ($count >= $max_partages) {
            $pdo->exec("DELETE FROM analyses_partagees ORDER BY date_creation ASC LIMIT 1");
        }

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