<?php
$page_title = "Statistiques des Documents";
$active_page = "stats";
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Vérifier si admin
if($_SESSION['user_role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Récupérer les données statistiques
$documents_by_type = $document->countDocumentsByType();
$documents_by_status = $document->countDocumentsByStatus();
$documents_by_user = $document->getDocumentsByUser();

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
            height: 100%;
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
        .btn-primary {
            background-color: var(--mauve-fonce);
            border-color: var(--mauve-fonce);
        }
        
        .btn-primary:hover {
            background-color: var(--mauve-froid);
            border-color: var(--mauve-froid);
        }
        
        /* Stats specific */
        .stats-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--rose-bebe), var(--mauve-froid));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .chart-container {
            position: relative;
            height: 350px; /* Hauteur augmentée pour mieux voir les graphiques */
            width: 100%;
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
        
        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .stat-card:nth-child(2) {
            animation-delay: 0.1s;
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

                <!-- Stats Content -->
                <div class="stats-container">
                    <div class="page-header">
                        <h2 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistiques des Documents</h2>
                    </div>

                    <!-- Première ligne avec 2 graphiques -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Répartition par Type</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="typeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="stat-card card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Statut des Documents</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deuxième ligne avec 1 seul graphique centré -->
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="stat-card card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Documents par Utilisateur</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="userChart"></canvas>
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Palette de couleurs personnalisée
        const colors = {
            mauve: {
                default: 'rgba(179, 153, 212, 1)',
                light: 'rgba(179, 153, 212, 0.7)',
                veryLight: 'rgba(179, 153, 212, 0.3)'
            },
            rose: {
                default: 'rgba(244, 194, 194, 1)',
                light: 'rgba(244, 194, 194, 0.7)'
            },
            success: {
                default: 'rgba(168, 216, 185, 1)'
            },
            warning: {
                default: 'rgba(255, 232, 161, 1)'
            },
            secondary: {
                default: 'rgba(196, 196, 196, 1)'
            }
        };

        // Options communes pour tous les graphiques
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 20,
                        font: {
                            size: 12,
                            family: "'Poppins', sans-serif"
                        },
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    titleFont: {
                        size: 13,
                        weight: 'bold',
                        family: "'Poppins', sans-serif"
                    },
                    bodyFont: {
                        size: 12,
                        family: "'Poppins', sans-serif"
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw;
                            return label;
                        }
                    }
                },
                datalabels: {
                    display: false
                }
            }
        };

        // Graphique des types
        new Chart(document.getElementById('typeChart'), {
            type: 'pie',
            data: {
                labels: [<?php foreach($documents_by_type as $type) echo "'".ucfirst($type->type)."',"; ?>],
                datasets: [{
                    data: [<?php foreach($documents_by_type as $type) echo $type->count.','; ?>],
                    backgroundColor: [
                        colors.mauve.default,
                        colors.rose.default,
                        colors.success.default,
                        colors.warning.default,
                        colors.secondary.default
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        formatter: (value) => {
                            return value > 0 ? value : '';
                        }
                    }
                },
                cutout: '65%'
            },
            plugins: [ChartDataLabels]
        });

        // Graphique des statuts
        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: [<?php foreach($documents_by_status as $status) echo "'".ucfirst(str_replace('_', ' ', $status->status))."',"; ?>],
                datasets: [{
                    label: 'Nombre de documents',
                    data: [<?php foreach($documents_by_status as $status) echo $status->count.','; ?>],
                    backgroundColor: [
                        colors.mauve.default,
                        colors.rose.default,
                        colors.success.default,
                        colors.warning.default,
                        colors.secondary.default
                    ],
                    borderRadius: 8,
                    borderWidth: 0,
                    barPercentage: 0.7
                }]
            },
            options: {
                ...chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 1
                        },
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    ...chartOptions.plugins,
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Graphique par utilisateur
        new Chart(document.getElementById('userChart'), {
            type: 'doughnut',
            data: {
                labels: [<?php foreach($documents_by_user as $user) echo "'".$user->username."',"; ?>],
                datasets: [{
                    data: [<?php foreach($documents_by_user as $user) echo $user->count.','; ?>],
                    backgroundColor: [
                        colors.mauve.default,
                        colors.rose.default,
                        colors.success.default,
                        colors.warning.default,
                        colors.secondary.default,
                        '#e74a3b',
                        '#6610f2',
                        '#fd7e14'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                ...chartOptions,
                cutout: '75%',
                plugins: {
                    ...chartOptions.plugins,
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        formatter: (value) => {
                            return value > 0 ? value : '';
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    });
    </script>
</body>
</html>