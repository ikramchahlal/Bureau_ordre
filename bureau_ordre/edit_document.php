<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérifier si l'ID du document est présent
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$document = new Document($db);
$user = new User($db);
$doc = $document->getDocumentById($_GET['id']);

// Vérifier les permissions
if (!$doc || ($_SESSION['user_role'] !== 'admin' && $doc->created_by != $_SESSION['user_id'])) {
    $_SESSION['error_msg'] = 'Accès non autorisé';
    header('Location: documents.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour du document
    if (isset($_POST['update_document'])) {
        $data = [
            'id' => $_POST['id'],
            'title' => $_POST['title'],
            'type' => $_POST['type'],
            'sender' => $_POST['sender'],
            'recipient' => $_POST['recipient'],
            'date_reception' => $_POST['date_reception'],
            'date_creation' => $_POST['date_creation'],
            'subject' => $_POST['subject'],
            'keywords' => $_POST['keywords'],
            'status' => $_POST['status'],
            'file_path' => $doc->file_path
        ];

        if ($document->updateDocument($data)) {
            $_SESSION['success_msg'] = 'Document mis à jour avec succès';
        } else {
            $_SESSION['error_msg'] = 'Erreur lors de la mise à jour';
        }
    }
    
    // Envoi aux utilisateurs
    if (isset($_POST['send_to_users']) && !empty($_POST['recipients'])) {
        $successCount = 0;
        foreach ($_POST['recipients'] as $recipient_id) {
            if ($document->sendDocumentToUser($doc->id, $recipient_id, $_SESSION['user_id'])) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            $_SESSION['success_msg'] = "Document envoyé à $successCount utilisateur(s)";
        } else {
            $_SESSION['error_msg'] = 'Erreur lors de l\'envoi du document';
        }
    }
    
    header('Location: edit_document.php?id=' . $doc->id);
    exit();
}

// Récupérer les données
$all_users = $user->getAllUsers();
$recipients = $document->getDocumentRecipients($doc->id);

$page_title = "Modifier Document";
$active_page = "documents";

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
        
        .card-header h4 {
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
        
        /* Form styles */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--mauve-froid);
            box-shadow: 0 0 0 0.25rem rgba(179, 153, 212, 0.25);
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
        
        /* Document edit specific */
        .document-edit-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .recipients-list {
            max-height: 200px;
            overflow-y: auto;
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

                <!-- Document Edit Form -->
                <div class="document-edit-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0" style="color: var(--mauve-fonce);">
                            <i class="fas fa-edit me-2"></i>Modification du document
                        </h3>
                        <a href="view_document.php?id=<?= $doc->id ?>" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i> Voir le document
                        </a>
                    </div>

                    <!-- Messages d'alerte -->
                    <?php if (isset($_SESSION['error_msg'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error_msg']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_msg']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $_SESSION['success_msg']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_msg']); ?>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Left Column - Document Form -->
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Informations du document</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?php echo $doc->id; ?>">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Référence</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($doc->reference); ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Titre*</label>
                                                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($doc->title); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Type*</label>
                                                <select class="form-select" name="type" required>
                                                    <option value="entrant" <?php echo $doc->type == 'entrant' ? 'selected' : ''; ?>>Entrant</option>
                                                    <option value="sortant" <?php echo $doc->type == 'sortant' ? 'selected' : ''; ?>>Sortant</option>
                                                    <option value="interne" <?php echo $doc->type == 'interne' ? 'selected' : ''; ?>>Interne</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Expéditeur</label>
                                                <input type="text" class="form-control" name="sender" value="<?php echo htmlspecialchars($doc->sender); ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Destinataire</label>
                                                <input type="text" class="form-control" name="recipient" value="<?php echo htmlspecialchars($doc->recipient); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Date Réception*</label>
                                                <input type="date" class="form-control" name="date_reception" value="<?php echo htmlspecialchars($doc->date_reception); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Date Création</label>
                                                <input type="date" class="form-control" name="date_creation" value="<?php echo htmlspecialchars($doc->date_creation); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Sujet</label>
                                            <textarea class="form-control" name="subject" rows="2"><?php echo htmlspecialchars($doc->subject); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Mots-clés</label>
                                            <input type="text" class="form-control" name="keywords" value="<?php echo htmlspecialchars($doc->keywords); ?>">
                                            <small class="text-muted">Séparez les mots-clés par des virgules</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Statut*</label>
                                            <select class="form-select" name="status" required>
                                                <option value="nouveau" <?php echo $doc->status == 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                                                <option value="en_cours" <?php echo $doc->status == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                                                <option value="traité" <?php echo $doc->status == 'traité' ? 'selected' : ''; ?>>Traité</option>
                                                <option value="archivé" <?php echo $doc->status == 'archivé' ? 'selected' : ''; ?>>Archivé</option>
                                            </select>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <a href="documents.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i> Annuler
                                            </a>
                                            <button type="submit" name="update_document" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Recipients -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Envoyer à d'autres utilisateurs</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?php echo $doc->id; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Sélectionnez les destinataires</label>
                                            <select class="form-select" name="recipients[]" multiple size="5">
                                                <?php foreach ($all_users as $u): ?>
                                                    <?php if ($u->id != $_SESSION['user_id']): ?>
                                                        <option value="<?php echo $u->id; ?>" 
                                                            <?php 
                                                            $is_recipient = false;
                                                            foreach ($recipients as $r) {
                                                                if ($r->recipient_id == $u->id) {
                                                                    $is_recipient = true;
                                                                    break;
                                                                }
                                                            }
                                                            echo $is_recipient ? 'selected' : ''; 
                                                            ?>>
                                                            <?php echo htmlspecialchars($u->full_name ?? $u->username); ?>
                                                            <?php if (isset($u->role)): ?>
                                                                (<?php echo ucfirst($u->role); ?>)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs</small>
                                        </div>
                                        
                                        <button type="submit" name="send_to_users" class="btn btn-primary w-100">
                                            <i class="fas fa-paper-plane me-2"></i>Envoyer
                                        </button>
                                    </form>
                                    
                                    <?php if (!empty($recipients)): ?>
                                        <hr>
                                        <h6>Destinataires actuels:</h6>
                                        <div class="recipients-list">
                                            <ul class="list-group list-group-flush">
                                                <?php foreach ($recipients as $r): ?>
                                                    <?php $recipient_user = $user->getUserById($r->recipient_id); ?>
                                                    <?php if ($recipient_user): ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                            <span>
                                                                <i class="fas fa-user-circle me-1" style="color: var(--mauve-froid);"></i>
                                                                <?php echo htmlspecialchars($recipient_user->full_name ?? $recipient_user->username); ?>
                                                            </span>
                                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($r->created_at)); ?></small>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
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