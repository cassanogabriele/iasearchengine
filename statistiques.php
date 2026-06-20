<?php 
require 'config.php';
require_once 'fonctions.php';
?>

<!DOCTYPE html>
    <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="style.css">
            <title>IA Search Engine - Performances</title>           
        </head>

        <body class="bg-light">    
            <div class="container mt-5">
                <h1 class="mb-4"><i class="fa-brands fa-searchengin"></i> IA Search Engine</h1>

                <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center" role="alert" style="border-left: 5px solid #ffc107;">
                    <i class="fa-solid fa-triangle-exclamation me-3" style="font-size: 1.5rem; color: #ffc107;"></i>
                    <div>
                        Ce site est un site de démonstration, ayant pour objectif de présenter mes compétences en intégration d'IA.
                    </div>
                </div>

                <a href="index.php" class="btn btn-secondary mb-3">← Retour</a>
                <div class="card p-4">
                    <h3>Statistiques de Performance IA</h3>
                    <canvas id="perfChart"></canvas>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
            fetch('stats.php') 
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('perfChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(item => item.jour),
                            datasets: [{
                                label: 'Latence (ms)',
                                data: data.map(item => item.avg_time),
                                borderColor: '#0d6efd',
                                tension: 0.1
                            }]
                        }
                    });
                });
            </script>
        </body>
    </html>