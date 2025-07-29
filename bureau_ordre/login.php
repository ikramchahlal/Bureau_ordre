
<?php
require_once 'includes/config.php';

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $loggedInUser = $user->login($username, $password);
    
    if ($loggedInUser) {
        $_SESSION['user_id'] = $loggedInUser->id;
        $_SESSION['user_role'] = $loggedInUser->role;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --rose-bebe: #F4C2C2;
            --mauve-froid: #E0B0FF;
            --mauve-froid-dark: #B57EDC;
            --rose-bebe-light: #FFE6E6;
        }
        
        body {
            background: linear-gradient(135deg, var(--rose-bebe), var(--mauve-froid));
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            animation: fadeInUp 0.8s;
            perspective: 1000px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transform-style: preserve-3d;
            transition: all 0.5s ease;
            overflow: hidden;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--mauve-froid), var(--rose-bebe));
            color: white;
            text-align: center;
            padding: 25px;
            border-bottom: none;
        }
        
        .card-header h3 {
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #eee;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--mauve-froid);
            box-shadow: 0 0 0 0.25rem rgba(224, 176, 255, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(to right, var(--mauve-froid), var(--mauve-froid-dark));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(181, 126, 220, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(181, 126, 220, 0.4);
            background: linear-gradient(to right, var(--mauve-froid-dark), var(--mauve-froid));
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--mauve-froid);
        }
        
        .alert {
            border-radius: 10px;
            animation: shake 0.5s;
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .decoration {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
        }
        
        .decoration-1 {
            width: 150px;
            height: 150px;
            top: 10%;
            left: 5%;
            animation: floating 4s ease-in-out infinite;
        }
        
        .decoration-2 {
            width: 100px;
            height: 100px;
            bottom: 15%;
            right: 8%;
            animation: floating 5s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="decoration decoration-1"></div>
    <div class="decoration decoration-2"></div>
    
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-container col-md-5 col-lg-4">
            <div class="login-card card shadow-lg">
                <div class="card-header">
                    <h3 class="mb-0">Connexion</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm">
                        <div class="mb-4">
                            <label for="username" class="form-label fw-bold">Nom d'utilisateur</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Entrez votre nom d'utilisateur">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Mot de passe</label>
                            <div class="input-group password-container">
                                <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Entrez votre mot de passe">
                                <span class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-login btn-block">
                                <span id="loginText">Se connecter</span>
                                <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
        
        // Form submission animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-login');
            const loginText = document.getElementById('loginText');
            const loginSpinner = document.getElementById('loginSpinner');
            
            submitBtn.disabled = true;
            loginText.textContent = 'Connexion...';
            loginSpinner.classList.remove('d-none');
            
            // Animation
            submitBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                submitBtn.style.transform = 'scale(1)';
            }, 300);
        });
        
        // Input focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.querySelector('label').style.color = 'var(--mauve-froid-dark)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.parentElement.querySelector('label').style.color = '';
            });
        });
    </script>
</body>
</html>
