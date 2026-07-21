<?php
require_once 'config.php';
header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT favoris FROM recherches WHERE id = ?");
    $stmt->execute([$id]);
    $fiche = $stmt->fetch();

    if ($fiche) {
        $nouveauFavori = ($fiche['favoris'] == 1) ? 0 : 1;
        $update = $pdo->prepare("UPDATE recherches SET favoris = ? WHERE id = ?");
        $update->execute([$nouveauFavori, $id]);
        echo json_encode(['status' => 'success', 'favoris' => $nouveauFavori]);
        exit;
    }
}
echo json_encode(['status' => 'error']);