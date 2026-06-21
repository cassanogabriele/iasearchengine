<?php 
require 'vendor/autoload.php';
use Dompdf\Dompdf;

if (isset($_POST['html_content'])) {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($_POST['html_content']);
    $dompdf->render();
    $dompdf->stream("comparaison.pdf");
} else {
    die("Aucun contenu à exporter.");
}
?>