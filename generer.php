<?php
set_time_limit(240); 
header('Content-Type: application/json');

require 'config.php';
require_once 'fonctions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['produit'])) {
    $nom = htmlspecialchars($_POST['produit']);
    $caract = htmlspecialchars($_POST['caract']);

    $prompt = "Tu es un expert. Analyse '$nom' ($caract). 
    Réponds EXCLUSIVEMENT en JSON valide. Pas de texte, pas d'explication.
    Format : {\"RESUME\": \"texte\", \"DESCRIPTION\": \"texte\", \"FIABILITE\": 90, \"INCERTITUDE\": \"texte\"}";

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

        sauvegarderRecherche($nom, $caract, $description, $resume, $fiabilite, $incertitude);
        
        echo json_encode(['status' => 'success', 'resume' => $resume, 'description' => $description, 'fiabilite' => $fiabilite, 'incertitude' => $incertitude]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Le JSON est mal formé.', 'raw' => $json_str]);
    }
    exit();
}