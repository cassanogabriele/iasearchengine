<?php
$start_time = microtime(true);

set_time_limit(240); 
header('Content-Type: application/json');

require 'config.php';
require_once 'fonctions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['produit'])) {
    $nom = htmlspecialchars($_POST['produit']);
    $caract = htmlspecialchars($_POST['caract']);

    // Un prompt textuel simple et direct que ton petit modèle comprendra sans bugger
    $prompt = "Fais une description détaillée, complète et professionnelle sur : $nom. $caract";

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
    $description = trim($result['response'] ?? '');

    if (!empty($description)) {
        $resume = substr($description, 0, 150) . "..."; // Un mini résumé automatique
        $fiabilite = 90;
        $incertitude = "Aucune";
        
        $end_time = microtime(true);
        $execution_time = round(($end_time - $start_time) * 1000); 
        $token_count = round(str_word_count($description) * 1.3);
        $word_count = str_word_count($description);    

        if ($word_count > 150) {
            $complexite = "Technique / Avancé";
        } elseif ($word_count > 80) {
            $complexite = "Intermédiaire";
        } else {
            $complexite = "Standard";
        }

        $textLower = mb_strtolower($description);

        if (strpos($textLower, 'historique') !== false || strpos($textLower, 'depuis') !== false) {
            $tonalite = "Historique / Narratif";
        } elseif (strpos($textLower, 'analyse') !== false || strpos($textLower, 'système') !== false) {
            $tonalite = "Analytique / Neutre";
        } else {
            $tonalite = "Professionnel / Informatif";
        }
        
        if (strpos($caract, 'Optimisation') !== false) {
            $nom .= " (optimisé par IA)";
        }

        sauvegarderRecherche(
            $nom, 
            $caract, 
            $description, 
            $resume, 
            $fiabilite, 
            $incertitude, 
            $execution_time, 
            $token_count, 
            $word_count,
            $tonalite
        );

        echo json_encode([
            'status' => 'success', 
            'resume' => $resume, 
            'description' => $description, 
            'fiabilite' => $fiabilite, 
            'incertitude' => $incertitude,
            'execution_time' => $execution_time,
            'token_count' => $token_count,
            'word_count' => $word_count,
            'complexite' => $complexite,
            'tonalite' => $tonalite
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'L\'IA n\'a rien renvoyé.']);
    }
    
    exit();
}