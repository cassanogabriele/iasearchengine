<?php
header('Content-Type: application/json');
require 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Correction : On extrait les infos proprement, qu'il s'agisse d'un objet ou d'une chaîne
    $n1 = is_array($input['item1']) ? ($input['item1']['nom'] ?? 'Inconnu') : $input['item1'];
    $r1 = is_array($input['item1']) ? ($input['item1']['resume'] ?? 'Pas de résumé') : 'Pas de résumé';
    
    $n2 = is_array($input['item2']) ? ($input['item2']['nom'] ?? 'Inconnu') : $input['item2'];
    $r2 = is_array($input['item2']) ? ($input['item2']['resume'] ?? 'Pas de résumé') : 'Pas de résumé';

    $prompt = "Tu es un expert. Compare {$n1} et {$n2}. 
    Données : 
    - {$n1}: {$r1}
    - {$n2}: {$r2}
    
    Réponds EXCLUSIVEMENT en code HTML brut. N'utilise aucun markdown.
    Structure le résultat avec :
    <h3>Analyse : {$n1} vs {$n2}</h3>
    <h4>Points communs</h4><ul><li>[Liste]</li></ul>
    <h4>Différences</h4><ul><li>[Liste]</li></ul>
    <h4>Tableau synthétique</h4>
    <table border='1' style='width:100%; border-collapse:collapse;'>
      <tr><th>Critère</th><th>{$n1}</th><th>{$n2}</th></tr>
      <tr><td>Genre</td><td>Info</td><td>Info</td></tr>
    </table>";

    $data = ["model" => AI_MODEL, "prompt" => $prompt, "stream" => false];
    
    $ch = curl_init(API_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo json_encode(['status' => 'error', 'message' => curl_error($ch)]);
    } else {
        $result = json_decode($response, true);
        // On vérifie si 'response' existe avant d'envoyer
        $analyse = $result['response'] ?? 'Erreur : Aucune réponse reçue de l\'IA.';
        echo json_encode(['status' => 'success', 'analyse' => $analyse]);
    }
    curl_close($ch);
}
?>