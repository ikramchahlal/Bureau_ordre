<?php
$page_title = "Ajouter un Document";
$active_page = "add_document";
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Mode édition
$edit_mode = isset($_GET['edit']);
$document_data = null;

if ($edit_mode) {
    $doc_id = $_GET['edit'];
    $document_data = $document->getDocumentById($doc_id);
    
    if (!$document_data) {
        $_SESSION['error_msg'] = "Document introuvable";
        header('Location: documents.php');
        exit();
    }
    
    // Vérifier que l'utilisateur est admin ou propriétaire du document
    if ($_SESSION['user_role'] != 'admin' && $document_data->created_by != $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "Vous n'avez pas la permission de modifier ce document";
        header('Location: documents.php');
        exit();
    }
    
    $page_title = "Modifier le Document";
}

// Récupérer tous les utilisateurs pour la sélection des destinataires
$users = $user->getAllUsers();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'title' => trim($_POST['title']),
        'type' => $_POST['type'],
        'sender' => trim($_POST['sender']),
        'recipient' => trim($_POST['recipient']),
        'date_reception' => $_POST['date_reception'],
        'date_creation' => $_POST['date_creation'],
        'subject' => trim($_POST['subject']),
        'keywords' => trim($_POST['keywords']),
        'status' => $_POST['status'],
        'created_by' => $_SESSION['user_id'],
        'reference' => trim($_POST['reference'])
    ];

    // Gestion du fichier
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = UPLOAD_DIR;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . '/' . $new_filename;

            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $target_path)) {
                $data['file_path'] = $new_filename;
                
                // Supprimer ancien fichier en mode édition
                if ($edit_mode && $document_data->file_path && file_exists($upload_dir . '/' . $document_data->file_path)) {
                    unlink($upload_dir . '/' . $document_data->file_path);
                }
            }
        } else {
            $_SESSION['error_msg'] = "Type de fichier non autorisé. Formats acceptés: " . implode(', ', $allowed_ext);
            header('Location: ' . $_SERVER['PHP_SELF'] . ($edit_mode ? '?edit=' . $doc_id : ''));
            exit();
        }
    } elseif ($edit_mode) {
        $data['file_path'] = $document_data->file_path;
    }

    // Enregistrement
    if ($edit_mode) {
        $data['id'] = $doc_id;
        
        if ($document->updateDocument($data)) {
            $_SESSION['success_msg'] = "Document mis à jour avec succès";
            header('Location: view_document.php?id=' . $doc_id);
            exit();
        }
    } else {
        try {
            if ($document->addDocument($data)) {
                $document_id = $db->lastInsertId();
                
                // Gestion des destinataires
                if (!empty($_POST['recipients'])) {
                    foreach ($_POST['recipients'] as $recipient_id) {
                        $document->sendDocumentToUser($document_id, $recipient_id, $_SESSION['user_id']);
                    }
                }
                
                $_SESSION['success_msg'] = "Document ajouté avec succès";
                header('Location: documents.php');
                exit();
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $_SESSION['error_msg'] = "La référence existe déjà. Veuillez en choisir une autre.";
            } else {
                $_SESSION['error_msg'] = "Erreur lors de l'enregistrement";
            }
            header('Location: add_document.php');
            exit();
        }
    }
    
    $_SESSION['error_msg'] = "Erreur lors de l'enregistrement";
}

// Générer référence si nouveau document
if (!$edit_mode) {
    $type = $_POST['type'] ?? 'entrant';
    $reference = $document->generateReference($type);
} else {
    $reference = $document_data->reference;
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
        
        .card-header h3 {
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
        
        /* Document add/edit specific */
        .document-form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
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

                <!-- Document Form -->
                <div class="document-form-container">
                    <div class="card shadow">
                        <div class="card-header">
                            <h3 class="mb-0">
                                <i class="fas <?= $edit_mode ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                                <?= $edit_mode ? 'Modifier le Document' : 'Ajouter un Document' ?>
                            </h3>
                        </div>
                        <div class="card-body">
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

                            <form method="post" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field">Référence</label>
                                            <input type="text" class="form-control" name="reference" 
                                                   value="<?= htmlspecialchars($reference) ?>" required>
                                            <small class="text-muted">Format recommandé: TYPE-ANNEE-NUM (ex: ENT-2023-001)</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field">Type de Document</label>
                                            <select class="form-select" name="type" id="doc_type" required>
                                                <option value="entrant" <?= ($edit_mode && $document_data->type == 'entrant') ? 'selected' : '' ?>>Entrant</option>
                                                <option value="sortant" <?= ($edit_mode && $document_data->type == 'sortant') ? 'selected' : '' ?>>Sortant</option>
                                                <option value="interne" <?= ($edit_mode && $document_data->type == 'interne') ? 'selected' : '' ?>>Interne</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label required-field">Titre</label>
                                            <input type="text" class="form-control" name="title" value="<?= $edit_mode ? htmlspecialchars($document_data->title) : '' ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" id="sender_label">Expéditeur</label>
                                            <input type="text" class="form-control" name="sender" id="sender_field" 
                                                   value="<?= $edit_mode ? htmlspecialchars($document_data->sender) : '' ?>"
                                                   <?= (!$edit_mode || $document_data->type == 'entrant') ? 'required' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" id="recipient_label">Destinataire</label>
                                            <input type="text" class="form-control" name="recipient" id="recipient_field"
                                                   value="<?= $edit_mode ? htmlspecialchars($document_data->recipient) : '' ?>"
                                                   <?= ($edit_mode && $document_data->type == 'sortant') ? 'required' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field">Date de Réception</label>
                                            <input type="date" class="form-control" name="date_reception" 
                                                   value="<?= $edit_mode ? $document_data->date_reception : date('Y-m-d') ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date du Document</label>
                                            <input type="date" class="form-control" name="date_creation" 
                                                   value="<?= $edit_mode ? $document_data->date_creation : date('Y-m-d') ?>">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label required-field">Objet</label>
                                            <textarea class="form-control" name="subject" rows="3" required><?= $edit_mode ? htmlspecialchars($document_data->subject) : '' ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mots-clés (séparés par des virgules)</label>
                                            <input type="text" class="form-control" name="keywords" 
                                                   value="<?= $edit_mode ? htmlspecialchars($document_data->keywords) : '' ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field">Statut</label>
                                            <select class="form-select" name="status" required>
                                                <option value="nouveau" <?= ($edit_mode && $document_data->status == 'nouveau') ? 'selected' : '' ?>>Nouveau</option>
                                                <option value="en_cours" <?= ($edit_mode && $document_data->status == 'en_cours') ? 'selected' : '' ?>>En Cours</option>
                                                <option value="traité" <?= ($edit_mode && $document_data->status == 'traité') ? 'selected' : '' ?>>Traité</option>
                                                <option value="archivé" <?= ($edit_mode && $document_data->status == 'archivé') ? 'selected' : '' ?>>Archivé</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Fichier Joint</label>
                                            <input type="file" class="form-control" name="document_file">
                                            <?php if ($edit_mode && $document_data->file_path): ?>
                                                <div class="mt-2">
                                                    <small>Fichier actuel: </small>
                                                    <a href="<?= URL_ROOT . '/' . UPLOAD_DIR . '/' . $document_data->file_path ?>" target="_blank">
                                                        <?= $document_data->file_path ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Envoyer à des utilisateurs (optionnel)</label>
                                            <select class="form-select" name="recipients[]" multiple>
                                                <?php foreach($users as $u): ?>
                                                    <?php if($u->id != $_SESSION['user_id']): ?>
                                                        <option value="<?= $u->id ?>" <?= ($edit_mode && $document->isRecipient($doc_id, $u->id)) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($u->username) ?> (<?= htmlspecialchars($u->full_name) ?>)
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs utilisateurs</small>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-end">
                                            <a href="<?= $edit_mode ? 'view_document.php?id=' . $doc_id : 'documents.php' ?>" 
                                               class="btn btn-outline-secondary me-2">
                                                <i class="fas fa-times me-1"></i> Annuler
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> <?= $edit_mode ? 'Mettre à jour' : 'Enregistrer' ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const docType = document.getElementById('doc_type');
        const senderField = document.getElementById('sender_field');
        const recipientField = document.getElementById('recipient_field');
        const senderLabel = document.getElementById('sender_label');
        const recipientLabel = document.getElementById('recipient_label');

        function updateFields() {
            const type = docType.value;
            
            if (type === 'entrant') {
                senderField.required = true;
                recipientField.required = false;
                senderLabel.classList.add('required-field');
                recipientLabel.classList.remove('required-field');
            } else if (type === 'sortant') {
                senderField.required = false;
                recipientField.required = true;
                senderLabel.classList.remove('required-field');
                recipientLabel.classList.add('required-field');
            } else {
                senderField.required = false;
                recipientField.required = false;
                senderLabel.classList.remove('required-field');
                recipientLabel.classList.remove('required-field');
            }
        }

        // Initialisation
        updateFields();
        
        // Écouteur de changement
        docType.addEventListener('change', updateFields);
    });
    </script>
</body>
</html>