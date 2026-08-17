<?php
$start_time = microtime(true);

set_time_limit(240); 
header('Content-Type: application/json');

require 'config.php';
require_once 'fonctions.php';

try {
    // On récupère l'historique et on limite aux 20 dernières entrées pour éviter le timeout
    $recherches = recupererHistorique();
    if (is_array($recherches)) {
        $recherches = array_slice($recherches, 0, 20);
    }
    
    if (empty($recherches)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Aucune recherche disponible pour générer un briefing.'
        ]);
        exit;
    }

    // On extrait les noms des produits
    $sujets = array_column($recherches, 'nom_produit');
    $listeSujets = implode(', ', array_unique($sujets));

    // Un prompt direct et court adapté au modèle
    $prompt = "Fais un court rapport d'analyse professionnel (en français, format Markdown) sur les tendances qui se dégagent de ces recherches récentes : [$listeSujets].";

    $data = [
        "model" => defined('AI_MODEL') ? AI_MODEL : 'llama3.2:1b', 
        "prompt" => $prompt, 
        "stream" => false
    ];
    
    $ch = curl_init(API_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        echo json_encode(['status' => 'error', 'message' => 'Curl Error: ' . $curl_error]);
        exit;
    }

    $result = json_decode($response, true);
    $analyseTexte = trim($result['response'] ?? '');

    if (!empty($analyseTexte)) {
        echo json_encode([
            'status' => 'success', 
            'briefing' => $analyseTexte
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'L\'IA n\'a rien renvoyé.']);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur technique : ' . $e->getMessage()
    ]);
}

exit();