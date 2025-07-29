<?php
$page_title = "Détails du Document";
$active_page = "documents";
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérification de l'ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_msg'] = "ID de document invalide";
    header('Location: documents.php');
    exit();
}

$doc_id = (int)$_GET['id'];

// Récupération du document avec vérification des permissions
$doc = $document->getDocumentById($doc_id);

if(!$doc) {
    error_log("Access denied for document $doc_id by user ".$_SESSION['user_id']);
    $_SESSION['error_msg'] = "Document introuvable ou accès non autorisé";
    header('Location: documents.php');
    exit();
}

// Vérification des permissions
$is_owner = ($doc->created_by == $_SESSION['user_id']);
$is_recipient = $document->isRecipient($doc_id, $_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] == 'admin');

if(!$is_admin && !$is_owner && !$is_recipient) {
    $_SESSION['error_msg'] = "Vous n'avez pas accès à ce document";
    header('Location: documents.php');
    exit();
}

// Récupérer les destinataires (visible seulement pour admin/propriétaire)
$recipients_info = [];
if ($is_admin || $is_owner) {
    try {
        $db->query('SELECT u.username, u.full_name, dr.created_at 
                   FROM document_recipients dr 
                   JOIN users u ON dr.recipient_id = u.id 
                   WHERE dr.document_id = :doc_id');
        $db->bind(':doc_id', $doc_id);
        $recipients_info = $db->resultSet();
    } catch (PDOException $e) {
        error_log("Error fetching recipients: " . $e->getMessage());
        $_SESSION['error_msg'] = "Erreur lors de la récupération des destinataires";
    }
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
        
        .card-header h5 {
            font-weight: 600;
            margin: 0;
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
        
        /* Document details specific */
        .document-details {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .detail-section {
            border-bottom: 1px solid rgba(179, 153, 212, 0.2);
            padding: 20px 0;
        }
        
        .detail-section:last-child {
            border-bottom: none;
        }
        
        .detail-section h5 {
            color: var(--mauve-fonce);
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--rose-bebe);
        }
        
        .subject-content {
            background-color: rgba(244, 194, 194, 0.1);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--mauve-froid);
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            border: 2px solid var(--rose-bebe);
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
                                Documents recus
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
                                Deconnexion
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

                <!-- Document Details -->
                <div class="document-details">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0" style="color: var(--mauve-fonce);">
                            <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($doc->title); ?>
                        </h3>
                        <a href="<?= $is_recipient ? 'received_documents.php' : 'documents.php' ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                    </div>

                    <!-- Document Status Badge -->
                    <div class="mb-4">
                        <?php 
                        $status_class = '';
                        switch($doc->status) {
                            case 'nouveau': $status_class = 'bg-info'; break;
                            case 'en_cours': $status_class = 'bg-warning text-dark'; break;
                            case 'traité': $status_class = 'bg-success'; break;
                            case 'archivé': $status_class = 'bg-secondary'; break;
                        }
                        ?>
                        <span class="badge <?= $status_class ?> me-2"><?= ucfirst(str_replace('_', ' ', $doc->status)) ?></span>
                        
                        <?php 
                        $badge_class = '';
                        switch($doc->type) {
                            case 'entrant': $badge_class = 'bg-primary'; break;
                            case 'sortant': $badge_class = 'bg-success'; break;
                            case 'interne': $badge_class = 'bg-secondary'; break;
                        }
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= ucfirst($doc->type) ?></span>
                    </div>

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Basic Info -->
                            <div class="detail-section">
                                <h5><i class="fas fa-info-circle me-2"></i>Informations de Base</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="40%">Référence:</th>
                                            <td><?= htmlspecialchars($doc->reference) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Créé par:</th>
                                            <td><?= htmlspecialchars($doc->created_by_name) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date de création:</th>
                                            <td><?= date('d/m/Y H:i', strtotime($doc->created_at)) ?></td>
                                        </tr>
                                        <?php if($doc->updated_at): ?>
                                        <tr>
                                            <th>Mis à jour le:</th>
                                            <td><?= date('d/m/Y H:i', strtotime($doc->updated_at)) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>

                            <!-- File & Keywords -->
                            <div class="detail-section">
                                <h5><i class="fas fa-paperclip me-2"></i>Fichier & Mots-clés</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="40%">Fichier:</th>
                                            <td>
                                                <?php if($doc->file_path): ?>
                                                    <a href="<?= URL_ROOT . '/' . UPLOAD_DIR . '/' . htmlspecialchars($doc->file_path) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download me-1"></i> Télécharger
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Aucun fichier attaché</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Mots-clés:</th>
                                            <td>
                                                <?php 
                                                $keywords = explode(',', $doc->keywords);
                                                foreach($keywords as $keyword) {
                                                    if(trim($keyword) != '') {
                                                        echo '<span class="badge bg-light text-dark me-1 mb-1">' . htmlspecialchars(trim($keyword)) . '</span>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Parties Concernées -->
                            <div class="detail-section">
                                <h5><i class="fas fa-users me-2"></i>Parties Concernées</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="40%"><?= $doc->type == 'entrant' ? 'Expéditeur:' : 'Destinataire:' ?></th>
                                            <td><?= htmlspecialchars($doc->type == 'entrant' ? $doc->sender : $doc->recipient) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date Réception:</th>
                                            <td><?= date('d/m/Y', strtotime($doc->date_reception)) ?></td>
                                        </tr>
                                        <?php if (!empty($recipients_info)): ?>
                                        <tr>
                                            <th>Envoyé à:</th>
                                            <td>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($recipients_info as $recipient): ?>
                                                        <li class="mb-1">
                                                            <i class="fas fa-user-circle me-1" style="color: var(--mauve-froid);"></i>
                                                            <?= htmlspecialchars($recipient->full_name) ?> 
                                                            <small class="text-muted">(<?= htmlspecialchars($recipient->username) ?>)</small>
                                                            <br>
                                                            <small class="text-muted ms-3"><i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($recipient->created_at)) ?></small>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="detail-section">
                                <h5><i class="fas fa-cog me-2"></i>Actions</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($is_admin || $is_owner): ?>
                                        <a href="edit_document.php?id=<?= $doc->id ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i> Modifier
                                        </a>
                                        <a href="delete_document.php?id=<?= $doc->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?');">
                                            <i class="fas fa-trash-alt me-1"></i> Supprimer
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($is_recipient): ?>
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check me-1"></i> Marquer comme lu
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Subject -->
                    <div class="detail-section">
                        <h5><i class="fas fa-align-left me-2"></i>Objet du Document</h5>
                        <div class="subject-content">
                            <?= nl2br(htmlspecialchars($doc->subject)) ?>
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