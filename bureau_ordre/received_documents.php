<?php
$page_title = "Documents Reçus";
$active_page = "received";
require_once 'includes/config.php';
require_once 'includes/auth.php';

$received_documents = $document->getReceivedDocuments($_SESSION['user_id']);

// Récupérer l'utilisateur actuel
$current_user = $user->getUserById($_SESSION['user_id']);
$user_display_name = $current_user->full_name ?? $current_user->username;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Police Google -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --rose-bebe: #f4c2c2;
            --mauve-froid: #b399d4;
            --mauve-fonce: #8a6dae;
            --gris-clair: #f8f9fa;
            --gris-moyen: #e9ecef;
            --texte-fonce: #343a40;
            --texte-clair: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: var(--texte-fonce);
        }
        
        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--mauve-froid), var(--mauve-fonce));
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 5px;
            margin: 5px 0;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        /* Header */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        
        /* Card styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--rose-bebe), var(--mauve-froid));
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        /* Badges */
        .badge {
            padding: 6px 10px;
            font-weight: 500;
            font-size: 0.8rem;
            border-radius: 50px;
        }
        
        .bg-primary {
            background-color: var(--mauve-froid) !important;
        }
        
        .bg-success {
            background-color: #a8d8b9 !important;
        }
        
        .bg-secondary {
            background-color: #c4c4c4 !important;
        }
        
        .bg-info {
            background-color: #b3e0ff !important;
            color: var(--texte-fonce) !important;
        }
        
        .bg-warning {
            background-color: #ffe8a1 !important;
            color: var(--texte-fonce) !important;
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--mauve-fonce);
            border-color: var(--mauve-fonce);
        }
        
        .btn-primary:hover {
            background-color: var(--mauve-froid);
            border-color: var(--mauve-froid);
        }
        
        .btn-outline-primary {
            color: var(--mauve-fonce);
            border-color: var(--mauve-fonce);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--mauve-fonce);
            border-color: var(--mauve-fonce);
            color: white;
        }
        
        /* Table styles */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--mauve-froid);
            color: white;
            border-bottom: none;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(244, 194, 194, 0.1);
        }
        
        /* Alert messages */
        .alert {
            border-radius: 8px;
        }
        
        /* User profile */
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            border: 2px solid var(--rose-bebe);
        }
        
        /* Document list specific */
        .documents-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .btn-group .btn {
            border-radius: 8px !important;
            margin: 0 2px;
        }
        
        /* DataTables customization */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 5px 10px;
            margin-bottom: 15px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            margin: 0 2px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--mauve-froid) !important;
            border-color: var(--mauve-froid) !important;
            color: white !important;
        }
        
        /* Page header */
        .page-header {
            background: linear-gradient(135deg, var(--rose-bebe), var(--mauve-froid));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold" style="color: white;">Gestion Documents</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'documents' ? 'active' : ''; ?>" href="documents.php">
                                <i class="fas fa-file-alt"></i>
                                Documents
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'add_document' ? 'active' : ''; ?>" href="add_document.php">
                                <i class="fas fa-plus-circle"></i>
                                Ajouter
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'search' ? 'active' : ''; ?>" href="search.php">
                                <i class="fas fa-search"></i>
                                Recherche
                            </a>
                        </li>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'stats' ? 'active' : ''; ?>" href="stats.php">
                                <i class="fas fa-chart-bar"></i>
                                Statistiques
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'received' ? 'active' : ''; ?>" href="received_documents.php">
                                <i class="fas fa-inbox"></i>
                                Documents reçus
                            </a>
                        </li>
                       
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'Paramètres' ? 'active' : ''; ?>" href="settings.php">
                                <i class="fas fa-cog"></i>
                                Paramètres 
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_page == 'parametres' ? 'active' : ''; ?>" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <span class="nav-link active"><?php echo $page_title; ?></span>
                                </li>
                            </ul>
                            
                            <div class="d-flex align-items-center">
                                <div class="user-profile">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_display_name); ?>&background=b399d4&color=fff" alt="Profile">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($user_display_name); ?></div>
                                        <small class="text-muted"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Documents List -->
                <div class="documents-container">
                    <div class="page-header">
                        <h2 class="mb-0"><i class="fas fa-inbox me-2"></i>Documents Reçus</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <?php if(empty($received_documents)): ?>
                                <div class="alert alert-info">Aucun document reçu pour le moment.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="receivedTable">
                                        <thead>
                                            <tr>
                                                <th>Référence</th>
                                                <th>Titre</th>
                                                <th>Expéditeur</th>
                                                <th>Date Réception</th>
                                                <th>Date d'Envoi</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($received_documents as $doc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc->reference); ?></td>
                                                <td><?php echo htmlspecialchars($doc->title); ?></td>
                                                <td><?php echo htmlspecialchars($doc->sender_name); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($doc->date_reception)); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($doc->received_at)); ?></td>
                                                <td>
                                                    <?php 
                                                    $status_class = '';
                                                    switch($doc->status) {
                                                        case 'nouveau': $status_class = 'bg-info'; break;
                                                        case 'en_cours': $status_class = 'bg-warning text-dark'; break;
                                                        case 'traité': $status_class = 'bg-success'; break;
                                                        case 'archivé': $status_class = 'bg-secondary'; break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $doc->status)); ?></span>
                                                </td>
                                                <td>
                                                    <a href="view_document.php?id=<?php echo $doc->id; ?>" class="btn btn-outline-primary btn-sm" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser DataTables sans le sélecteur de nombre d'éléments
        $('#receivedTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json',
                search: "Rechercher:",
                zeroRecords: "Aucun document trouvé",
                info: "Affichage de _START_ à _END_ sur _TOTAL_ documents",
                infoEmpty: "Aucun document disponible",
                infoFiltered: "(filtrés à partir de _MAX_ documents au total)",
                paginate: {
                    first: "Premier",
                    last: "Dernier",
                    next: "Suivant",
                    previous: "Précédent"
                }
            },
            lengthChange: false, // Désactive le sélecteur de nombre d'éléments
            order: [[4, 'desc']], // Tri par date d'envoi décroissante
            responsive: true,
            dom: '<"top"f>rt<"bottom"ip><"clear">', // Configuration sans lengthMenu
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
            }
        });
    });
    </script>
</body>
</html>