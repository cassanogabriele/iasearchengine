<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'fonctions.php'; 

// Les recherches actives
$recherches = recupererHistorique(); 
// Les recherches archivées
$archives = recupererArchives();
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
                $derniers = array_slice($recherches, 0, 5);
                foreach ($derniers as $fiche): 
                    $dateFr = date('d/m/Y H:i', strtotime($fiche['date_creation']));
                ?>
                    <div class="col-12">
                        <div class="preview-item p-3 rounded-3 d-flex justify-content-between align-items-center">
                            <div>                                    
                                <h5 class="mb-1"><i class="fa-solid fa-circle-check puce-success me-3"></i><?php echo htmlspecialchars($fiche['nom_produit']); ?></h5>
                                <span class="badge bg-info mb-1 text-light shadow-sm" style="font-size: 0.65rem; letter-spacing: 1px;">
                                     IA ENGINE
                                </span>
                                <span class="badge text-bg-success"><i class="fa-regular fa-clock me-1"></i> <?php echo $dateFr; ?></span>
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
                                        data-description="<?php echo htmlspecialchars($fiche['description_ia'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fa-solid fa-eye me-1"></i> Voir le résultat
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

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
                                        <div class="md-render-direct"><?php echo htmlspecialchars($donnees['description_ia']); ?></div>
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

            <div class="text-center mt-5">
                <button class="btn btn-link text-muted text-decoration-none small" onclick="confirmSuppr('supprimer_tout.php', true)">
                    <i class="fa-solid fa-power-off me-1"></i> Réinitialiser les recherches
                </button>
            </div>
        </div>

        <div class="modal fade swal-bootstrap-modal" id="previewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content text-light border-0 p-4">
                    <div class="modal-body text-center p-0">
                        <div class="swal-bootstrap-icon swal-bootstrap-success mb-4">
                            <i class="fa-solid fa-brain"></i>
                        </div>
                        <h3 class="fw-bold text-white mb-3" id="modal-product-name" style="font-size: 1.8rem;"></h3>
                        <div id="modal-description" class="md-render text-start p-4 rounded-3 mb-4" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); max-height: 400px; overflow-y: auto;"></div>
                    </div>
                    <div class="d-flex justify-content-center w-100">
                        <button type="button" class="btn swal-btn-close fw-bold px-5 py-2" data-bs-dismiss="modal">Fermer l'aperçu</button>
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

            // Affichage détaillé du résultat de la recherche
            const bModal = new bootstrap.Modal(document.getElementById('previewModal'));

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-voir-resultat');
                if(btn) {
                    const name = btn.getAttribute('data-nom');
                    const content = btn.getAttribute('data-description');
                    document.getElementById('modal-product-name').innerText = name;
                    document.getElementById('modal-description').innerHTML = marked.parse(content);
                    bModal.show();
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

                fetch('generer.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()) // On attend du JSON maintenant
                .then(data => {
                    if (data.status === 'error') {
                        Swal.fire({
                            icon: 'warning', 
                            title: 'Attention',
                            text: data.message, 
                            confirmButtonColor: '#f39c12'
                        });
                    } else {
                        // Succès : on affiche le message de succès puis on recharge
                        statusZone.innerHTML = `
                            <div class="alert alert-success shadow-sm border-0">
                                <i class="fa-solid fa-check-circle me-2"></i> 
                                Recherche réussie ! Mise à jour...
                            </div>`;
                        this.reset();
                        setTimeout(() => location.reload(), 1000); 
                    }
                })
                .catch(err => {
                    statusZone.innerHTML = `<div class="alert alert-danger shadow-sm border-0"><i class="fa-solid fa-exclamation-triangle me-2"></i> Erreur système.</div>`;
                })
                .finally(() => {
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
                if (idAArchiver && boutonDeclencheur) {
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
                            const blocApercu = boutonDeclencheur.closest('.col-12');
                            const ligneTableau = boutonDeclencheur.closest('tr');
                            
                            if (blocApercu) blocApercu.remove();
                            if (ligneTableau) ligneTableau.remove();
                        } else {
                            console.error("Erreur serveur :", data);
                        }
                    })
                    .catch(err => {
                        archiveModal.hide();
                        console.error("Erreur réseau :", err);
                    });
                }
            });

           document.getElementById('toggle-archives').addEventListener('click', function() {
                const sectionActives = document.getElementById('section-apercu');
                const sectionExplo = document.getElementById('section-exploration');
                const sectionArchives = document.getElementById('section-archives');
                const viewTitle = document.getElementById('view-title');

                // On définit l'état : si les archives sont cachées, on veut les afficher
                const showArchives = sectionArchives.classList.contains('hidden-section');

                if (showArchives) {
                    // Afficher les archives, cacher le reste
                    sectionArchives.classList.remove('hidden-section');
                    sectionActives.classList.add('hidden-section');
                    sectionExplo.classList.add('hidden-section');
                    
                    viewTitle.innerText = "Recherches archivées";
                    this.innerHTML = "<i class='fa-solid fa-clock-rotate-left me-2'></i> Recherches actives";
                } else {
                    // Revenir aux actives (mode aperçu par défaut)
                    sectionArchives.classList.add('hidden-section');
                    sectionActives.classList.remove('hidden-section');
                    
                    viewTitle.innerText = "Dernières recherches";
                    this.innerHTML = "<i class='fa-solid fa-eye me-2'></i> Recherches archivées";
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
                .then(response => response.json()) // On convertit bien la réponse en JSON
                .then(data => {
                    if (data.status === 'success') {
                        // Succès : on rafraîchit
                        location.reload(); 
                    } else {
                        console.warn("Le serveur a répondu, mais pas avec le succès attendu :", data);
                    }
                })
                .catch(err => {
                    // C'est ici que l'erreur 404 ou réseau serait capturée
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
            
        </script>
    </body>
</html>
