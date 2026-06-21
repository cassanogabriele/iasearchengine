<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'fonctions.php'; 

// Les recherches actives
$recherches = recupererHistorique(); 
// Les recherches archivées
$archives = recupererArchives();

// Configuration de la pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$parPage = 4; // ON CHANGE ICI : 4 éléments au lieu de 5
$totalRecherches = count($recherches);
$nombreDePages = ceil($totalRecherches / $parPage);
$offset = ($page - 1) * $parPage;

// On récupère uniquement la tranche pour la page actuelle
$recherchesPaginees = array_slice($recherches, $offset, $parPage);
?>

<!DOCTYPE html>
    <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="style.css">
            <title>IA Search Engine</title>           
        </head>

        <body class="p-5 bg-light">
            <div id="loader-overlay">
                <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5 class="mt-3 text-primary">L'IA génère votre réponse...</h5>
            </div>

            <div class="container">
                <h1 class="mb-4"><i class="fa-brands fa-searchengin"></i> IA Search Engine</h1>

                <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center" role="alert" style="border-left: 5px solid #ffc107;">
                    <i class="fa-solid fa-triangle-exclamation me-3" style="font-size: 1.5rem; color: #ffc107;"></i>
                    <div>
                        Ce site est un site de démonstration, ayant pour objectif de présenter mes compétences en intégration d'IA.
                    </div>
                </div>
                
                <div class="card p-4 mb-4 shadow-sm border-0">
                    <form id="searchForm" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="produit" class="form-control" placeholder="Que recherchez-vous ?" required>
                        </div>

                        <div class="col-md-5">
                            <input type="text" name="caract" class="form-control" placeholder="Caractéristiques de recherche">
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-magnifying-glass me-2"></i> Rechercher</button>
                        </div>
                    </form>
                </div>

                <div id="ajax-status"></div>

                <div class="mb-4">
                    <div class="input-group shadow-sm rounded border border-secondary" style="background: #1e1e24;">
                        <span class="input-group-text bg-dark text-light border-0"><i class="fa-solid fa-filter text-info"></i></span>
                        <input type="text" id="filterInput" class="form-control bg-dark text-light border-0" placeholder="Filtrer l'historique instantanément (Nom, contenu...)..." style="box-shadow: none;">
                    </div>
                </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 id="view-title">Dernières recherches</h3>

                    <div>      
                        <a href="statistiques.php" class="btn btn-info text-white">
                            <i class="fa-solid fa-chart-line"></i> Voir les Statistiques
                        </a>

                        <button class="btn btn-warning rounded-pill px-4 shadow-sm" onclick="lancerComparaisonIA()">
                            <i class="fa-solid fa-code-compare me-2"></i> Comparer 
                        </button>

                        <button id="toggle-archives" class="btn btn-success rounded-pill px-4 shadow-sm me-2">
                            <i class="fa-solid fa-eye me-2"></i> Recherches archivées
                        </button>
                        
                        <button id="toggle-view" class="btn btn-dark rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-table-list me-2"></i> Mode Exploration
                        </button>
                    </div>
                </div>

                <div id="section-apercu" class="row g-3">
                    <?php 
                    foreach ($recherchesPaginees as $fiche): 
                        $dateFr = date('d/m/Y H:i', strtotime($fiche['date_creation']));
                    ?>

                        <div class="col-12">
                            <div class="preview-item p-3 rounded-3 d-flex justify-content-between align-items-center">
                                <div>                                
                                    <input type="checkbox" 
                                        class="select-fiche" 
                                        value="<?php echo htmlspecialchars($fiche['id'], ENT_QUOTES, 'UTF-8'); ?>" 
                                        data-nom="<?php echo htmlspecialchars($fiche['nom_produit'], ENT_QUOTES, 'UTF-8'); ?>" 
                                        data-resume="<?php echo htmlspecialchars($fiche['resume'], ENT_QUOTES, 'UTF-8'); ?>">

                                    <h5 class="mb-1 d-inline"><i class="fa-solid fa-circle-check puce-success me-3"></i><?php echo htmlspecialchars($fiche['nom_produit']); ?></h5>
                                    <span class="badge bg-info mb-1 text-light shadow-sm" style="font-size: 0.65rem; letter-spacing: 1px;">IA ENGINE</span>
                                    <span class="badge text-bg-success"><i class="fa-regular fa-clock me-1"></i> <?php echo $dateFr; ?></span>

                                    <span class="badge <?php echo ($fiche['fiabilite'] > 70) ? 'bg-primary' : 'bg-warning'; ?> shadow-sm">
                                        <i class="fa-solid fa-gauge-high"></i> Fiabilité : <?php echo $fiche['fiabilite']; ?>%
                                    </span>
                                    
                                    <button class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($fiche['incertitude']); ?>">
                                        <i class="fa-solid fa-circle-question text-muted"></i>
                                    </button>
                                </div>

                                <div class="d-flex gap-2 align-items-center">
                                    <button class="btn btn-outline-danger" onclick="confirmSuppr('supprimer.php?id=<?php echo $fiche['id']; ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="ouvrirModaleArchive(<?php echo $fiche['id']; ?>, this)">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-voir-resultat" 
                                            data-nom="<?php echo htmlspecialchars($fiche['nom_produit'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-description="<?php echo htmlspecialchars($fiche['description_ia'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-resume="<?php echo htmlspecialchars($fiche['resume'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-time="<?php echo (int)$fiche['execution_time']; ?>"
                                            data-tokens="<?php echo (int)$fiche['token_count']; ?>"
                                            data-words="<?php echo (int)$fiche['word_count']; ?>"> <i class="fa-solid fa-eye me-1"></i> Voir le résultat
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($nombreDePages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Précédent</a>
                        </li>
                        <?php for($i = 1; $i <= $nombreDePages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $nombreDePages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                <div id="section-exploration" class="hidden-section mt-4">
                    <div class="table-responsive card border-0 shadow-sm p-3">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Recherche</th>
                                    <th>Résultat</th>
                                    <th>Date de recherche</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($recherches as $donnees): ?>
                                    <tr>
                                        <td class="fw-bold" style="width: 15%;"><?php echo htmlspecialchars($donnees['nom_produit']); ?></td>

                                    <td class="description-cell py-3">
                                            <div class="md-render-direct mb-3">
                                                <?php echo htmlspecialchars($donnees['resume']); ?>
                                            </div>

                                            <div class="d-flex justify-content-end">
                                                <button class="btn btn-outline-primary btn-voir-resultat" 
                                                        data-nom="<?php echo htmlspecialchars($donnees['nom_produit'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-description="<?php echo htmlspecialchars($donnees['description_ia'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa-solid fa-eye me-1"></i> Voir plus
                                                </button>
                                            </div>
                                        </td>

                                        <td style="width: 15%;">
                                            <span class="badge rounded-pill bg-success text-light border shadow-sm p-2">
                                                <i class="fa-regular fa-calendar-days me-1 text-light"></i> 
                                                <?php echo date('d/m/Y', strtotime($donnees['date_creation'])); ?> 
                                                <span class="text-light ms-1"><?php echo date('H:i', strtotime($donnees['date_creation'])); ?></span>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmSuppr('supprimer.php?id=<?php echo $donnees['id']; ?>')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="section-archives" class="hidden-section mt-4">                
                    <?php if (empty($archives)): ?>
                        <div class="alert alert-info shadow-sm border-0 d-flex align-items-center mt-3" role="alert">
                            <i class="fa-solid fa-circle-info me-3" style="font-size: 1.5rem;"></i>
                            <div>Aucune recherche archivée pour le moment.</div>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($archives as $fiche): ?>
                                <div class="col-12">
                                    <div class="preview-item p-3 rounded-3 border border-warning d-flex justify-content-between align-items-center">
                                        <h5><?php echo htmlspecialchars($fiche['nom_produit']); ?></h5>
                                        <button class="btn btn-outline-success" onclick="desarchiver(<?php echo $fiche['id']; ?>)">
                                            <i class="fa-solid fa-box-open"></i> Désarchiver
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>  
                
                <div class="text-center mt-5 mb-5">
                    <button class="btn btn-primary text-light text-decoration-none small" onclick="confirmSuppr('supprimer_tout.php', true)">
                        <i class="fa-solid fa-power-off me-1"></i> Réinitialiser les recherches
                    </button>
                </div>
                
                <footer class="footer mt-auto py-5 bg-white border-top">
                    <div class="container">
                        <div class="row align-items-center">
                            
                            <div class="col-md-6 text-center text-md-start">
                                <h6 class="text-uppercase fw-bold mb-1">IA Search Engine</h6>
                                <p class="text-muted small mb-0">
                                    &copy; <?php echo date('Y'); ?> 
                                    <a href="https://gabriel-cassano.be/" class="text-decoration-none text-primary fw-bold">Gabriele Cassano</a>
                                </p>
                            </div>

                            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                                <div class="d-inline-flex align-items-center bg-light p-2 px-3 rounded-pill border shadow-sm">
                                    <span class="text-muted small me-2 text-uppercase fw-bold" style="font-size: 0.7rem;">Model</span>
                                    <span class="badge bg-dark">
                                        <i class="fa-solid fa-microchip me-1 text-info"></i> 
                                        <?php echo defined('AI_MODEL') ? AI_MODEL : 'llama3.2:1b'; ?>
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </footer>           

                <button id="backToTop" class="btn btn-primary rounded-circle shadow" 
                        style="position: fixed; bottom: 30px; right: 30px; display: none; z-index: 9999;">
                    <i class="fa-solid fa-arrow-up"></i>
                </button>
            </div>

            <div class="modal fade swal-bootstrap-modal" id="previewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content text-light border-0 p-4">
                        <div class="modal-body text-center p-0">
                            <div class="swal-bootstrap-icon swal-bootstrap-success mb-4">
                                <i class="fa-solid fa-brain"></i>
                            </div>
                            <h3 class="fw-bold text-white mb-3" id="modal-product-name" style="font-size: 1.8rem;"></h3>
                            
                            <div id="modal-description" class="md-render text-start p-4 rounded-3 mb-4" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); max-height: 300px; overflow-y: auto;"></div>
                        
                        </div>

                        <div class="d-flex justify-content-center w-100 mb-4">
                            <button type="button" class="btn btn-outline-light fw-bold px-4 py-2 me-2 text-black" onclick="copierTexte()">
                                <i class="fa-solid fa-copy me-1"></i> Copier
                            </button>

                            <button type="button" class="btn btn-sm btn-info text-light px-4 py-2 me-2" id="btn-generer-resume">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Résumé
                            </button>

                            <button type="button" class="btn swal-btn-close fw-bold px-5 py-2" data-bs-dismiss="modal">Fermer</button>
                        </div>

                        <div id="metrics-console" class="p-3 bg-dark border border-secondary rounded shadow-sm font-monospace text-start" style="font-size: 0.75rem; color: #00ff41;">
                            <div class="d-flex justify-content-between">
                                <span><i class="fa-solid fa-microchip me-1"></i>Status : <span id="api-status">OK</span></span>
                                <span><i class="fa-solid fa-clock me-1"></i>Latence: <span id="gen-time">0</span> ms</span>
                            </div>
                            <div class="mt-1">
                                <span><i class="fa-solid fa-font me-1"></i>Tokens: <span id="token-count">0</span></span>
                                <span class="ms-3"><i class="fa-solid fa-pen-nib me-1"></i>Mots: <span id="word-count">0</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade swal-bootstrap-modal" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                    <div class="modal-content text-light border-0 p-4">
                        <div class="modal-body text-center p-0">
                            <div class="swal-bootstrap-icon swal-bootstrap-danger mb-4">
                                <i class="fa-solid fa-xmark"></i>
                            </div>
                            <h3 class="fw-bold text-danger mb-2" style="font-size: 1.7rem;">Suppression</h3>
                            <p id="suppr-message" class="text-secondary px-2 mb-4" style="font-size: 0.95rem; line-height: 1.5;"></p>
                        </div>
                        <div class="d-flex justify-content-center gap-3 w-100">
                            <button type="button" class="btn swal-btn-cancel fw-semibold px-4 py-2" data-bs-dismiss="modal">Annuler</button>
                            <a id="btn-confirm-link" href="#" class="btn swal-btn-danger fw-bold px-4 py-2">Supprimer</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade swal-bootstrap-modal" id="confirmArchiveModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                    <div class="modal-content text-light border-0 p-4">
                        <div class="modal-body text-center p-0">
                            <div class="swal-bootstrap-icon swal-bootstrap-warning mb-4">
                                <i class="fa-solid fa-exclamation"></i>
                            </div>
                            <h3 class="fw-bold text-warning mb-2" style="font-size: 1.7rem;">Archivage</h3>
                            <p class="text-secondary px-2 mb-4" style="font-size: 0.95rem; line-height: 1.5;">
                                Êtes-vous sûr de vouloir archiver cette recherche ? Elle sera retirée des recherches actives.
                            </p>
                        </div>
                        <div class="d-flex justify-content-center gap-3 w-100">
                            <button type="button" class="btn swal-btn-cancel fw-semibold px-4 py-2" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" id="btn-confirm-archive" class="btn swal-btn-confirm fw-bold px-4 py-2">Confirmer l'archivage</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="modalDescription<?php echo $donnees['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Détails</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php echo htmlspecialchars($donnees['description']); ?>
                        </div>

                        <p class="text-muted small">
                            <i class="fa-solid fa-calculator"></i> 
                            Nombre de caractères : <span id="char-count">0</span>
                        </p>
                    </div>
                </div>
            </div>

            <div id="resumeModal" class="mt-3" style="display:none;">
                <h6>Résumé</h6>
                <div id="modal-resume-result" class="p-2 bg-light border rounded"></div>
            </div>

            <div class="modal fade" id="compareModal" tabindex="-1" aria-labelledby="compareModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title" id="compareModalLabel">
                                <i class="fa-solid fa-microchip me-2 text-info"></i> Analyse comparative IA
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body" id="compareModalBody">
                            <div class="text-center p-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-3">Préparation de l'analyse...</p>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light fw-bold px-4 py-2 me-2 text-black" onclick="copierAnalyse()">
                                <i class="fa-solid fa-copy me-1"></i> Copier
                            </button>

                            <form action="exporter.php" method="POST" id="pdfForm">
                                <input type="hidden" name="html_content" id="html_content_field">
                                <button type="button" class="btn btn-danger btn-sm" onclick="submitPdfForm()">
                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                </button>
                            </form>

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
            <script>
                const toggleBtn = document.getElementById('toggle-view');
                const sectionApercu = document.getElementById('section-apercu');
                const sectionExplo = document.getElementById('section-exploration');
                const viewTitle = document.getElementById('view-title');

                toggleBtn.addEventListener('click', function() {
                    const isExplo = sectionExplo.classList.toggle('hidden-section');
                    sectionApercu.classList.toggle('hidden-section');
                    if (!isExplo) {
                        document.querySelectorAll('.md-render-direct').forEach(el => {
                            if (!el.dataset.rendered) { el.innerHTML = marked.parse(el.innerText); el.dataset.rendered = "true"; }
                        });
                        this.innerHTML = "<i class='fa-solid fa-compress me-2'></i> Mode Aperçu";
                        viewTitle.innerText = "Historique complet";
                    } else {
                        this.innerHTML = "<i class='fa-solid fa-table-list me-2'></i> Mode Exploration";
                        viewTitle.innerText = "Dernières recherches";
                    }
                });

                const bModal = new bootstrap.Modal(document.getElementById('previewModal'));
                const btnAction = document.getElementById('btn-generer-resume'); // Ton bouton dans la modale

                document.querySelectorAll('.btn-voir-resultat').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const nom = this.getAttribute('data-nom');
                        const desc = this.getAttribute('data-description');
                        const resume = this.getAttribute('data-resume'); 
                        const time = this.getAttribute('data-time') || '0';
                        const tokens = this.getAttribute('data-tokens') || '0';
                        const words = this.getAttribute('data-words') || '0'; 

                        document.getElementById('gen-time').innerText = time;
                        document.getElementById('token-count').innerText = tokens;
                        document.getElementById('word-count').innerText = words;


                        // Stockage dans des variables globales pour que le bouton bascule y accède
                        window.currentDesc = desc;
                        window.currentResume = (resume && resume.trim() !== "") ? resume : "Aucun résumé disponible.";

                        // Injection initiale
                        document.getElementById('modal-product-name').innerText = nom;
                        const modalDesc = document.getElementById('modal-description');
                        modalDesc.innerHTML = marked.parse(window.currentDesc);

                        // Réinitialisation du bouton de bascule
                        const btnAction = document.getElementById('btn-generer-resume');
                        btnAction.dataset.state = "description";
                        // On remet l'icône + le texte
                        btnAction.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Résumé';

                        new bootstrap.Modal(document.getElementById('previewModal')).show();
                    });
                });


                // Gestion du basculement (toggle)
                btnAction.addEventListener('click', function() {
                    const modalDesc = document.getElementById('modal-description');
        
                    if (this.dataset.state === "description") {
                        // Passer au résumé
                        modalDesc.innerHTML = marked.parse(window.currentResume);
                        this.innerHTML = '<i class="fa-solid fa-align-left me-1"></i> Description complète';
                        this.dataset.state = "resume";
                    } else {
                        // Revenir à la description
                        modalDesc.innerHTML = marked.parse(window.currentDesc);
                        this.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Résumé';
                        this.dataset.state = "description";
                    }
                });

                // Suppression d'une recherche
                const supprModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));

                function confirmSuppr(url, isGlobal = false) {
                    document.getElementById('btn-confirm-link').href = url;
                    document.getElementById('suppr-message').innerText = isGlobal ? "Voulez-vous vider toute la base de données ?" : "Êtes-vous sûr de vouloir supprimer cette recherche ?";
                    supprModal.show();
                }           

                // Le moteur de recherche
                document.getElementById('searchForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const loader = document.getElementById('loader-overlay');
                    const statusZone = document.getElementById('ajax-status');
                    
                    loader.style.display = 'flex';
                    statusZone.innerHTML = '';

                    fetch('generer.php', { 
                        method: 'POST', 
                        body: new FormData(this) 
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'error') {
                            // Affichage de l'erreur via SweetAlert
                            Swal.fire({
                                icon: 'warning', 
                                title: 'Attention',
                                text: data.message, 
                                confirmButtonColor: '#f39c12'
                            });
                            loader.style.display = 'none'; 
                        } else {
                            // Succès : on affiche le message de succès puis on recharge la page
                            statusZone.innerHTML = `
                                <div class="alert alert-success shadow-sm border-0">
                                    <i class="fa-solid fa-check-circle me-2"></i> 
                                    Recherche réussie ! Mise à jour...
                                </div>`;
                            this.reset();
                            // Le rechargement permet de rafraîchir la liste via PHP
                            location.reload(); 
                        }
                    })
                    .catch(err => {
                        statusZone.innerHTML = `<div class="alert alert-danger shadow-sm border-0"><i class="fa-solid fa-exclamation-triangle me-2"></i> Erreur système.</div>`;
                        loader.style.display = 'none';
                    });
                });           

                // Archivage d'une recherche
                const archiveModal = new bootstrap.Modal(document.getElementById('confirmArchiveModal'));

                let idAArchiver = null;
                let boutonDeclencheur = null;

                function ouvrirModaleArchive(id, bouton) {
                    idAArchiver = id;
                    boutonDeclencheur = bouton;
                    archiveModal.show();
                }

                document.getElementById('btn-confirm-archive').addEventListener('click', function() {
                    if (!idAArchiver) return;

                    let formData = new FormData();
                    formData.append('id', idAArchiver);

                    fetch('archiver.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        archiveModal.hide();

                        if (data && data.status === 'success') {
                            const elApercu = document.querySelector(`.col-12[data-id="${idAArchiver}"]`);
                            const elTableau = document.querySelector(`tr[data-id="${idAArchiver}"]`);

                            // On définit le délai pour l'animation
                            const delai = 300; 

                            const supprimer = (el) => {
                                if (el) {
                                    el.style.transition = `opacity ${delai}ms ease`;
                                    el.style.opacity = "0";
                                }
                            };

                            supprimer(elApercu);
                            supprimer(elTableau);

                            Swal.fire({
                                icon: 'success',
                                title: 'Recherche archivée',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    // S'exécute quand l'alerte se ferme
                                    location.reload();
                                }
                            });
                        } else {
                            console.error("Erreur serveur :", data);
                            location.reload(); 
                        }
                    })
                    .catch(err => {
                        archiveModal.hide();
                        console.error("Erreur :", err);
                        location.reload();
                    });
                });
                        
                let modeExplorationActif = false;

                // Bouton "Recherches Archivées"
                document.getElementById('toggle-archives').addEventListener('click', function() {
                    const sArchives = document.getElementById('section-archives');
                    const sApercu = document.getElementById('section-apercu');
                    const sExplo = document.getElementById('section-exploration');

                    sArchives.classList.toggle('hidden-section');

                    if (!sArchives.classList.contains('hidden-section')) {
                        // Ouverture des archives : on masque tout le reste
                        sApercu.classList.add('hidden-section');
                        sExplo.classList.add('hidden-section');
                    } else {
                        // Fermeture des archives : on rétablit la vue précédente
                        if (modeExplorationActif) {
                            sExplo.classList.remove('hidden-section');
                        } else {
                            sApercu.classList.remove('hidden-section');
                        }
                    }
                });

                // Bouton "Mode Exploration"
                document.getElementById('toggle-view').addEventListener('click', function() {
                    const sApercu = document.getElementById('section-apercu');
                    const sExplo = document.getElementById('section-exploration');
                    const sArchives = document.getElementById('section-archives');

                    // On s'assure que les archives sont fermées
                    sArchives.classList.add('hidden-section');

                    // Bascule l'état
                    modeExplorationActif = !modeExplorationActif;

                    // Mise à jour de la visibilité
                    if (modeExplorationActif) {
                        sApercu.classList.add('hidden-section');
                        sExplo.classList.remove('hidden-section');
                        this.innerHTML = "<i class='fa-solid fa-compress me-2'></i> Mode Aperçu";
                    } else {
                        sApercu.classList.remove('hidden-section');
                        sExplo.classList.add('hidden-section');
                        this.innerHTML = "<i class='fa-solid fa-table-list me-2'></i> Mode Exploration";
                    }
                });
                
                // Désarchivage d'une recherche
                function desarchiver(id) {
                    let formData = new FormData();
                    formData.append('id', id);

                    fetch('desarchiver.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // On lance l'alerte
                            Swal.fire({
                                icon: 'success',
                                title: 'Recherche désarchivée',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    // S'exécute quand l'alerte se ferme
                                    location.reload();
                                }
                            });

                        } else {
                            console.warn("Le serveur a répondu, mais pas avec le succès attendu :", data);
                        }
                    })
                    .catch(err => {
                        console.error("Erreur critique lors de la connexion au serveur :", err);
                    });
                }

                function debounce(fonctionAExecuter, delaiEnMs) {
                    let minuteur; 
                    return function(...argumentsPasses) {
                        clearTimeout(minuteur);
                        minuteur = setTimeout(() => {
                            fonctionAExecuter.apply(this, argumentsPasses);
                        }, delaiEnMs);
                    };
                }

                function filtrerTableau() {
                    const recherche = document.getElementById('filterInput').value.toLowerCase();
                    const lignes = document.querySelectorAll('#section-exploration tbody tr');
                    lignes.forEach(ligne => {
                        const texteDeLaLigne = ligne.textContent.toLowerCase();
                        ligne.style.display = texteDeLaLigne.includes(recherche) ? "" : "none";
                    });

                    const blocs = document.querySelectorAll('#section-apercu .col-12');
                    blocs.forEach(bloc => {
                        const texteDuBloc = bloc.textContent.toLowerCase();
                        bloc.style.display = texteDuBloc.includes(recherche) ? "" : "none";
                    });
                }

                document.getElementById('filterInput').addEventListener('keyup', debounce(filtrerTableau, 300));
                
                function copierTexte() {
                    const texteACopier = document.getElementById('modal-description').innerText;
                    navigator.clipboard.writeText(texteACopier).then(() => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Copié dans le presse-papier',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    });
                }

                const content = btn.getAttribute('data-description');
                const parts = content.split('[RESUME]');
                const description = parts[0];
                const resume = parts[1] || "Aucun résumé disponible.";

                document.getElementById('modal-description').innerHTML = marked.parse(description);
                document.getElementById('modal-resume-text').innerHTML = marked.parse(resume);

                function updateMetrics(data) {
                    // Statut del'API
                    const statusEl = document.getElementById('api-status');
                    statusEl.innerText = data.success ? 'CONNECTED' : 'ERROR';
                    statusEl.style.color = data.success ? '#00ff41' : '#ff4d4d';

                    // Temps de génération
                    document.getElementById('gen-time').innerText = data.execution_time || '0';

                    // Calcul simple des tokens (approximatif : 1 mot ≈ 0.75 token)
                    const wordCount = data.content.split(' ').length;
                    document.getElementById('token-count').innerText = Math.round(wordCount * 1.3);
                }

                // Fonction à déclencher au clic sur un bouton "Comparer"    
                async function lancerComparaisonIA() {
                    const checkboxes = document.querySelectorAll('.select-fiche:checked');

                    if (checkboxes.length < 2) {
                        Swal.fire({
                                icon: 'warning', 
                                title: 'Attention',
                                text: 'Veuillez sélectionner au moins 2 résultas de recherche !', 
                                confirmButtonColor: '#f39c12'
                            });
                        return;
                    }

                    const modalElement = document.getElementById('compareModal');
                    const modalBody = document.getElementById('compareModalBody');
                    
                    // Récupérer ou créer l'instance Bootstrap correctement
                    let myModal = bootstrap.Modal.getInstance(modalElement);
                    
                    if (!myModal) {
                        myModal = new bootstrap.Modal(modalElement);
                    }
                    
                    modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-grow text-primary"></div><p>Analyse IA en cours...</p></div>';
                    myModal.show();

                    // Récupération des noms
                    const item1 = checkboxes[0].dataset.nom;
                    const item2 = checkboxes[1].dataset.nom;

                    // Appel à ton comparer.php
                    try {
                        const response = await fetch('comparer.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ item1: item1, item2: item2 })
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            modalBody.innerHTML = `<div class="p-3">${data.analyse}</div>`;
                        } else {
                            modalBody.innerHTML = `<div class="alert alert-danger">Erreur : ${data.message}</div>`;
                        }
                    } catch (error) {
                        modalBody.innerHTML = `<div class="alert alert-danger">Erreur de connexion avec l'IA.</div>`;
                    }
                }

                function copierAnalyse() {
                    const content = document.getElementById('compareModalBody').innerText;
                    navigator.clipboard.writeText(content).then(() => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Copié dans le presse-papier',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }).catch(err => {
                        console.error('Erreur de copie : ', err);
                    });
                }

                function submitPdfForm() {
                    // Récupérer le contenu
                    const contenu = document.getElementById('compareModalBody').innerHTML;
                    
                    // Debug : vérifie dans la console F12 si le contenu est bien là
                    console.log("Contenu capturé : ", contenu); 
                    
                    if (contenu.trim() === "") {
                        alert("Erreur : Le modal est vide !");
                        return;
                    }

                    // Injecter
                    document.getElementById('html_content_field').value = contenu;
                    
                    // Soumettre manuellement le formulaire
                    document.getElementById('pdfForm').submit();
                }
            </script>        
        </body>
    </html>
