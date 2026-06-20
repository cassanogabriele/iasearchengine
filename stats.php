<?php
require 'config.php';
require_once 'fonctions.php';
header('Content-Type: application/json');

$data = getStatistiquesPerformance();
echo json_encode($data);
?>