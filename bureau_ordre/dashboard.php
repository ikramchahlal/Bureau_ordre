<?php
$page_title = "Tableau de bord";
$active_page = "dashboard";
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Récupérer les documents récents
$recent_documents = $document->getDocuments(7);

// Récupérer l'utilisateur actuel
$current_user = $user->getUserById($_SESSION['user_id']);
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
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Header */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: 600;
            color: var(--mauve-fonce);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            border: 2px solid var(--rose-bebe);
        }
        
        /* Card */
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
        
        .card-header h5 {
            font-weight: 600;
            margin: 0;
        }
        
        /* Table */
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--gris-clair);
            border-bottom: 2px solid var(--mauve-froid);
            font-weight: 600;
            color: var(--mauve-fonce);
        }
        
        .table tbody tr {
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(244, 194, 194, 0.1);
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
        .btn-outline-primary {
            color: var(--mauve-fonce);
            border-color: var(--mauve-fonce);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--mauve-fonce);
            border-color: var(--mauve-fonce);
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
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
            <i class="fas fa-plus-circle"></i> <!-- Changé de fa-archive à fa-plus-circle -->
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
            <i class="fas fa-inbox"></i> <!-- Changé de fa-users à fa-inbox -->
            Documents recus
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link <?php echo $active_page == 'Paramètres' ? 'active' : ''; ?>" href="settings.php">
            <i class="fas fa-cog"></i> <!-- Changé de fa-chart-bar à fa-cog -->
            Paramètres 
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $active_page == 'parametres' ? 'active' : ''; ?>" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> <!-- Changé de fa-cog à fa-sign-out-alt -->
            Deconnexion
        </a>
    </li>
</ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white">
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
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($current_user->full_name ?? $current_user->username); ?>&background=b399d4&color=fff" alt="Profile">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($current_user->full_name ?? $current_user->username); ?></div>
                                        <small class="text-muted"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="container-fluid py-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Derniers Documents</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Référence</th>
                                                    <th>Titre</th>
                                                    <th>Type</th>
                                                    <th>Expéditeur/Destinataire</th>
                                                    <th>Date Réception</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(!empty($recent_documents)): ?>
                                                    <?php foreach($recent_documents as $doc): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($doc->reference); ?></td>
                                                        <td><?php echo htmlspecialchars(substr($doc->title, 0, 30) . (strlen($doc->title) > 30 ? '...' : '')); ?></td>
                                                        <td>
                                                            <?php 
                                                            $badge_class = '';
                                                            switch($doc->type) {
                                                                case 'entrant': $badge_class = 'bg-primary'; break;
                                                                case 'sortant': $badge_class = 'bg-success'; break;
                                                                case 'interne': $badge_class = 'bg-secondary'; break;
                                                                default: $badge_class = 'bg-secondary';
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($doc->type); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($doc->type == 'entrant' ? $doc->sender : $doc->recipient); ?>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($doc->date_reception)); ?></td>
                                                        <td>
                                                            <?php 
                                                            $status_class = '';
                                                            switch($doc->status) {
                                                                case 'nouveau': $status_class = 'bg-info'; break;
                                                                case 'en_cours': $status_class = 'bg-warning text-dark'; break;
                                                                case 'traité': $status_class = 'bg-success'; break;
                                                                case 'archivé': $status_class = 'bg-secondary'; break;
                                                                default: $status_class = 'bg-secondary';
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $doc->status)); ?></span>
                                                        </td>
                                                        <td>
    <a href="view_document.php?id=<?php echo $doc->id; ?>" class="btn btn-sm btn-outline-primary" title="Voir">
        <i class="fas fa-eye"></i>
    </a>
    <?php if ($_SESSION['user_role'] === 'admin' || $doc->created_by == $_SESSION['user_id']): ?>
        <a href="edit_document.php?id=<?php echo $doc->id; ?>" class="btn btn-sm btn-outline-warning" title="Modifier">
            <i class="fas fa-edit"></i>
        </a>
        <a href="delete_document.php?id=<?php echo $doc->id; ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?');">
            <i class="fas fa-trash-alt"></i>
        </a>
    <?php endif; ?>
</td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Aucun document récent</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>