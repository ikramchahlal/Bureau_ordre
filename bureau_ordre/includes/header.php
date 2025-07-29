<?php
// Initialisation des variables
if (!isset($active_page)) {
    $active_page = '';
}

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des infos utilisateur
$current_user = $user->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> | <?php echo $page_title ?? ''; ?></title>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar améliorée -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>">
                <i class="fas fa-archive me-2"></i><?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'documents' ? 'active' : ''; ?>" href="documents.php">
                            <i class="fas fa-file-alt me-1"></i> Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'add_document' ? 'active' : ''; ?>" href="add_document.php">
                            <i class="fas fa-plus-circle me-1"></i> Ajouter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'search' ? 'active' : ''; ?>" href="search.php">
                            <i class="fas fa-search me-1"></i> Recherche
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'received' ? 'active' : ''; ?>" href="received_documents.php">
                            <i class="fas fa-inbox me-1"></i> Reçus
                        </a>
                    </li>
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'stats' ? 'active' : ''; ?>" href="stats.php">
                            <i class="fas fa-chart-pie me-1"></i> Stats
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <i class="fas fa-user-circle fa-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($current_user->full_name ?? $current_user->username); ?></div>
                                    <small class="text-white-50"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Mon Profil</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4 animate-fade">
        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <div><?php echo htmlspecialchars($_SESSION['success_msg']); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div><?php echo htmlspecialchars($_SESSION['error_msg']); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>