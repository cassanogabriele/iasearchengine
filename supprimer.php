<?php
require_once 'fonctions.php';

if (isset($_GET['id'])) {
    supprimerFiche($_GET['id']);
}

header('Location: index.php');
exit();
?>