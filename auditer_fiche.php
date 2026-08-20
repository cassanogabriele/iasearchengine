<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("SELECT description_ia, nom_produit FROM recherches WHERE id = ?");
    $stmt->execute([$id]);
    $fiche = $stmt->fetch();

    if ($fiche) {
        $prompt = "Agis en tant qu'auditeur technique senior. Analyse ce texte sur '{$fiche['nom_produit']}' : \n\n{$fiche['description_ia']}\n\nFonde ton analyse en un court paragraphe (en français) pour pointer les limites, les manques ou les biais potentiels de cette génération.";

        $data = ["model" => AI_MODEL, "prompt" => $prompt, "stream" => false];
        
        $ch = curl_init(API_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        echo json_encode(['status' => 'success', 'audit' => trim($result['response'] ?? 'Pas d\'audit disponible.')]);
        exit;
    }
}
echo json_encode(['status' => 'error', 'message' => 'Erreur technique.']);