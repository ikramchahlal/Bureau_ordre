<?php
$page_title = "Gestion des Documents";
$active_page = "documents";
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérification supplémentaire pour la suppression
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $doc = $document->getDocumentById($id);
    
    // Empêcher la suppression si l'utilisateur n'est pas admin ou propriétaire
    if($_SESSION['user_role'] != 'admin' && $doc->created_by != $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "Vous n'avez pas la permission de supprimer ce document.";
        header('Location: documents.php');
        exit();
    }
    
    try {
        if($document->deleteDocument($id)) {
            $_SESSION['success_msg'] = "Document supprimé avec succès.";
        } else {
            $_SESSION['error_msg'] = "Erreur lors de la suppression du document.";
        }
    } catch(PDOException $e) {
        $_SESSION['error_msg'] = "Erreur lors de la suppression: " . $e->getMessage();
        error_log('Delete Error: ' . $e->getMessage());
    }
    header('Location: documents.php');
    exit();
}

// Récupérer tous les documents (filtrés automatiquement par la classe Document)
try {
    $all_documents = $document->getDocuments();
} catch(PDOException $e) {
    $error_msg = "Erreur lors de la récupération des documents: " . $e->getMessage();
    error_log($error_msg);
}

// Récupérer l'utilisateur actuel comme dans le dashboard
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
        }
        
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 5px;
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0" style="color: var(--mauve-fonce);">
                            <i class="fas fa-file-alt me-2"></i>Liste des Documents
                        </h3>
                        <a href="add_document.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Ajouter un Document
                        </a>
                    </div>

                    <?php if(isset($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error_msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="documentsTable">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Titre</th>
                                            <th>Type</th>
                                            <th>Expéditeur/Destinataire</th>
                                            <th>Date Réception</th>
                                            <th>Statut</th>
                                            <th>Créé par</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(isset($all_documents)): ?>
                                            <?php foreach($all_documents as $doc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc->reference); ?></td>
                                                <td><?php echo htmlspecialchars($doc->title); ?></td>
                                                <td>
                                                    <?php 
                                                    $badge_class = '';
                                                    switch($doc->type) {
                                                        case 'entrant': $badge_class = 'bg-primary'; break;
                                                        case 'sortant': $badge_class = 'bg-success'; break;
                                                        case 'interne': $badge_class = 'bg-secondary'; break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($doc->type); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($doc->type == 'entrant' ? $doc->sender : $doc->recipient); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($doc->date_reception)); ?></td>
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
                                                <td><?php echo htmlspecialchars($doc->created_by_name); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="view_document.php?id=<?php echo $doc->id; ?>" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if($_SESSION['user_role'] == 'admin' || $doc->created_by == $_SESSION['user_id']): ?>
                                                        <a href="add_document.php?edit=<?php echo $doc->id; ?>" class="btn btn-outline-success" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <?php if($_SESSION['user_role'] == 'admin'): ?>
                                                        <a href="documents.php?delete=<?php echo $doc->id; ?>" class="btn btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document?')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Aucun document trouvé</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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
        // Initialiser DataTables avec paramètres français et sans le sélecteur de nombre d'éléments
        $('#documentsTable').DataTable({
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
            order: [[4, 'desc']],
            responsive: true,
            dom: '<"top"f>rt<"bottom"ip><"clear">', // Retire le 'l' (lengthMenu) du DOM
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
            }
        });
    });
    </script>
</body>
</html>