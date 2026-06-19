<?php
$start_time = microtime(true);

set_time_limit(240); 
header('Content-Type: application/json');

require 'config.php';
require_once 'fonctions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['produit'])) {
    $nom = htmlspecialchars($_POST['produit']);
    $caract = htmlspecialchars($_POST['caract']);

    $prompt = "Tu es un expert. Analyse '$nom' ($caract). 
        Réponds EXCLUSIVEMENT en JSON valide. Pas de texte, pas d'explication.
        Format requis : {
            \"RESUME\": \"texte\", 
            \"DESCRIPTION\": \"texte\", 
            \"FIABILITE\": 90, 
            \"INCERTITUDE\": \"texte\",
            \"STATS\": {\"mots\": X, \"tokens\": Y}
        }";

    $data = ["model" => AI_MODEL, "prompt" => $prompt, "stream" => false];
    
    $ch = curl_init(API_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        echo json_encode(['status' => 'error', 'message' => 'Curl Error: ' . $curl_error]);
        exit;
    }

    $result = json_decode($response, true);
    $raw = trim($result['response'] ?? '');

    // Tentative de récupération propre
    $start = strpos($raw, '{');
    $end = strrpos($raw, '}');
    
    if ($start === false || $end === false) {
        echo json_encode(['status' => 'error', 'message' => 'L\'IA n\'a pas renvoyé d\'objet JSON', 'raw' => $raw]);
        exit;
    }

    $json_str = substr($raw, $start, $end - $start + 1);
    $data_ia = json_decode($json_str, true);

    if ($data_ia && isset($data_ia['RESUME'])) {
        $resume = $data_ia['RESUME'];
        $description = $data_ia['DESCRIPTION'] ?? "Pas de description.";
        $fiabilite = (int)($data_ia['FIABILITE'] ?? 0);
        $incertitude = $data_ia['INCERTITUDE'] ?? "Aucune";
        
        $end_time = microtime(true);
        $execution_time = round(($end_time - $start_time) * 1000); // en ms
        // Estimation : 1 mot ≈ 1.3 tokens (très couramment utilisé pour les estimations rapides)
        $token_count = round(str_word_count($resume . $description) * 1.3);
        $stats = $data_ia['STATS'] ?? [];
        $word_count = (int)($stats['mots'] ?? 0);      

        sauvegarderRecherche(
            $nom, 
            $caract, 
            $description, 
            $resume, 
            $fiabilite, 
            $incertitude, 
            $execution_time, 
            $token_count, 
            $word_count
        );

        echo json_encode([
            'status' => 'success', 
            'resume' => $resume, 
            'description' => $description, 
            'fiabilite' => $fiabilite, 
            'incertitude' => $incertitude,
            'execution_time' => $execution_time,
            'token_count' => $token_count,
            'word_count' => $word_count
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Le JSON est mal formé.', 'raw' => $json_str]);
    }
    exit();
}