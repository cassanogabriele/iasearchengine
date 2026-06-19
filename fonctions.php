<?php
require_once 'config.php';

// Récupère les fiches des recherches de la base de données
function recupererHistorique($limite = null) {
    global $pdo;
    $sql = "SELECT * FROM fiches_produits WHERE archive = 0 ORDER BY date_creation DESC";
    
    if ($limite !== null) {
        $sql .= " LIMIT " . (int)$limite;
    }
    
    $query = $pdo->query($sql);
    return $query->fetchAll();
}

// Vérification du cache
function verifierCache($nom_produit, $caract) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT description_ia FROM fiches_produits 
        WHERE LOWER(nom_produit) = LOWER(?) 
        AND LOWER(caract_cle) = LOWER(?) 
        AND date_creation > NOW() - INTERVAL 1 DAY 
        LIMIT 1
    ");
    $stmt->execute([$nom_produit, $caract]);
    $resultat = $stmt->fetch();
    
    return $resultat ? $resultat['description_ia'] : null;
}

// Sauvegarde de la nouvelle recherche en BDD avec les nouveaux champs
function sauvegarderRecherche($nom, $caract, $description, $resume, $fiabilite, $incertitude, $exec_time, $tokens, $mots) {
    global $pdo;

    // Il y a 11 colonnes listées, il faut 11 points d'interrogation
    $sql = "INSERT INTO fiches_produits 
            (nom_produit, caract_cle, description_ia, resume, date_creation, archive, fiabilite, incertitude, execution_time, token_count, word_count) 
            VALUES (?, ?, ?, ?, NOW(), 0, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // Tu dois envoyer exactement 9 variables pour remplir les 9 '?' manquants 
    // (date_creation et archive étant gérés en dur dans le SQL)
    $stmt->execute([
        $nom,          
        $caract,       
        $description,  
        $resume,      
        $fiabilite,    
        $incertitude,  
        $exec_time,    
        $tokens,       
        $mots          
    ]);
}

// Supprime une fiche de recherche par son ID
function supprimerFiche($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM fiches_produits WHERE id = ?");
    return $stmt->execute([$id]);
}

// Vider toute la table
function viderTout() {
    global $pdo;
    return $pdo->exec("TRUNCATE TABLE fiches_produits");
}

// Récupérer les recherches archivées
function recupererArchives() {
    global $pdo; 
    try {
        $stmt = $pdo->query("SELECT * FROM fiches_produits WHERE archive = 1 ORDER BY date_creation DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Désarchiver une recherche 
function desarchiverRecherche($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE fiches_produits SET archive = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        return false;
    }
}
?>