<?php
require_once 'config.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;

// Récupère les fiches des recherches de la base de données
function recupererHistorique($limite = null) {
    global $pdo;
    // On fait une jointure pour récupérer les tags concaténés par recherche
    $sql = "SELECT r.*, GROUP_CONCAT(t.nom SEPARATOR ',') as liste_tags 
            FROM recherches r
            LEFT JOIN recherche_tags rt ON r.id = rt.recherche_id
            LEFT JOIN tags t ON rt.tag_id = t.id
            WHERE r.archive = 0 
            GROUP BY r.id
            ORDER BY r.date_creation DESC";
    
    if ($limite !== null) {
        $sql .= " LIMIT " . (int)$limite;
    }
    
    $query = $pdo->query($sql);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Vérification du cache
function verifierCache($nom_produit, $caract) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT description_ia FROM recherches 
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
    $sql = "INSERT INTO recherches 
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

    // Récupérer l'id qu'on vient de créer 
    $recherche_id = $pdo->lastInsertId();

    $texte_complet = $nom . " " . $description . " " . $resume;
    extraireEtSauvegarderTags($pdo, $recherche_id, $texte_complet);
    
    return $recherche_id;
}

function extraireEtSauvegarderTags($pdo, $recherche_id, $texte) {
    try {
        $stmt = $pdo->query("SELECT id, nom FROM tags");
        $tags_disponibles = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $trouves = [];

        foreach ($tags_disponibles as $tag_id => $nom_tag) {
            // On vérifie si le tag est dans le texte
            if (stripos($texte, $nom_tag) !== false) {
                $pdo->prepare("INSERT IGNORE INTO recherche_tags (recherche_id, tag_id) VALUES (?, ?)")
                    ->execute([$recherche_id, $tag_id]);
                $trouves[] = $nom_tag;
            }
        }
        
        // DEBUG : On renvoie les tags trouvés pour voir si ça marche
        // Si tu vois 'tags_trouves' vide dans ta réponse réseau, c'est que le texte ne contient aucun tag.
        error_log("ID: $recherche_id | Texte analysé: " . substr($texte, 0, 50) . "... | Tags trouvés: " . implode(',', $trouves));
        
    } catch (Exception $e) {
        error_log("Erreur tags: " . $e->getMessage());
    }
}

// Supprime une fiche de recherche par son ID
function supprimerFiche($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM recherches WHERE id = ?");
    return $stmt->execute([$id]);
}

// Vider toute la table
function viderTout() {
    global $pdo;
    return $pdo->exec("TRUNCATE TABLE recherches");
}

// Récupérer les recherches archivées
function recupererArchives() {
    global $pdo; 
    try {
        $stmt = $pdo->query("SELECT * FROM recherches WHERE archive = 1 ORDER BY date_creation DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Désarchiver une recherche 
function desarchiverRecherche($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE recherches SET archive = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        return false;
    }
}

function getStatistiquesPerformance() {
    global $pdo;
    // On récupère les données pour le graphique
    $stmt = $pdo->query("SELECT 
            DATE(date_creation) as jour, 
            AVG(execution_time) as avg_time, 
            AVG(token_count) as avg_tokens 
            FROM recherches 
            GROUP BY DATE(date_creation) 
            ORDER BY jour ASC LIMIT 30");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function genererPDF($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM fiches_produits WHERE id = ?");
    $stmt->execute([$id]);
    $fiche = $stmt->fetch();

    $html = "<h1>Fiche : {$fiche['nom_produit']}</h1>
             <p><strong>Résumé:</strong> {$fiche['resume']}</p>
             <p><strong>Description:</strong> {$fiche['description_ia']}</p>
             <hr><small>Généré le: {$fiche['date_creation']} | Tokens: {$fiche['token_count']}</small>";

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("analyse_{$fiche['nom_produit']}.pdf");
}

// Récupèrer tous les tags uniques pour les fitres de recherche
function recupererTousLesTags() {
    global $pdo;
    try {
        // On récupère uniquement les noms des tags, triés par ordre alphabétique
        $stmt = $pdo->query("SELECT DISTINCT nom FROM tags ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération des tags: " . $e->getMessage());
        return [];
    }
}
?>