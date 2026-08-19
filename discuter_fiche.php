<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['question'])) {
    $id = (int)$_POST['id'];
    $question = trim($_POST['question']);

    if (empty($question)) {
        echo json_encode(['status' => 'error', 'message' => 'Question vide.']);
        exit;
    }

    try {
        // 1. Récupérer le contexte de la fiche
        $stmt = $pdo->prepare("SELECT description_ia FROM recherches WHERE id = ?");
        $stmt->execute([$id]);
        $fiche = $stmt->fetch();

        if (!$fiche) {
            echo json_encode(['status' => 'error', 'message' => 'Fiche introuvable.']);
            exit;
        }

        $contexte = $fiche['description_ia'];

        // 2. Préparer le prompt RAG pour Ollama
        $prompt = "Contexte : $contexte\n\nQuestion de l'utilisateur : $question\n\nInstructions : Réponds à la question en te basant UNIQUEMENT sur le contexte fourni. Si la réponse n'est pas dans le texte, dis-le clairement.";

        $data = [
            "model" => defined('AI_MODEL') ? AI_MODEL : 'llama3.2:1b', 
            "prompt" => $prompt, 
            "stream" => false
        ];
        
        // 3. Appel cURL à l'API Ollama
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
        $reponseTexte = trim($result['response'] ?? '');

        if (!empty($reponseTexte)) {
            echo json_encode([
                'status' => 'success', 
                'reponse' => $reponseTexte
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'L\'IA n\'a rien renvoyé.']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Requête invalide.']);
}
exit;