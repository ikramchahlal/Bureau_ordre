<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérifier si l'ID du document est présent
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$document = new Document($db);
$doc = $document->getDocumentById($_GET['id']);

// Vérifier les permissions (admin ou propriétaire)
if (!$doc || ($_SESSION['user_role'] !== 'admin' && $doc->created_by != $_SESSION['user_id'])) {
    $_SESSION['message'] = 'Accès non autorisé';
    $_SESSION['message_type'] = 'danger';
    header('Location: dashboard.php');
    exit();
}

// Supprimer le document
if ($document->deleteDocument($_GET['id'])) {
    $_SESSION['message'] = 'Document supprimé avec succès';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Erreur lors de la suppression';
    $_SESSION['message_type'] = 'danger';
}

header('Location: dashboard.php');
exit();