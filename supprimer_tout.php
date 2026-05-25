<?php
// On inclut la configuration pour accéder à $pdo
require_once 'config.php'; 

try {
    // La commande TRUNCATE vide la table et remet l'auto-incrément à zéro
    $pdo->exec("TRUNCATE TABLE fiches_produits");
    
    // Redirection vers l'accueil avec un message de succès
    header('Location: index.php?status=all_deleted');
} catch (PDOException $e) {
    // Affiche l'erreur réelle en cas de problème avec la base de données
    die("Erreur lors de la suppression totale : " . $e->getMessage());
}
exit();
?>