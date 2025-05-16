<?php
// login.php - Pagina di login
require_once 'config.php';

// Se l'utente è già loggato, reindirizza alla dashboard
if (isUserLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];

// Verifica se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Errore di validazione del form. Riprova.';
    } else {
        // Validazione input
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) ? true : false;
        
        // Validazione email
        if (empty($email)) {
            $errors[] = 'L\'email è obbligatoria';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email non è valida';
        }
        
        // Validazione password
        if (empty($password)) {
            $errors[] = 'La password è obbligatoria';
        }
        
        // Se non ci sono errori, procedi con il login
        if (empty($errors)) {
            try {
                $conn = getDbConnection();
                $stmt = $conn->prepare("SELECT id, password FROM utenti WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch();
                    
                    // Verifica la password
                    if (password_verify($password, $user['password'])) {
                        // Imposta la sessione
                        $_SESSION['user_id'] = $user['id'];
                        
                        // Aggiorna l'ultimo accesso
                        $stmt = $conn->prepare("UPDATE utenti SET ultimo_accesso = NOW() WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        
                        // Se l'utente ha selezionato "Ricordami", imposta un cookie
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expiry = time() + (30 * 24 * 60 * 60); // 30 giorni
                            
                            // Salva il token nel database
                            $stmt = $conn->prepare("UPDATE utenti SET remember_token = ?, token_expiry = FROM_UNIXTIME(?) WHERE id = ?");
                            $stmt->execute([$token, $expiry, $user['id']]);
                            
                            // Imposta il cookie
                            setcookie('remember_token', $token, $expiry, '/', '', false, true);
                        }
                        
                        // Reindirizza alla dashboard
                        redirect('dashboard.php');
                    } else {
                        $errors[] = 'Email o password non validi';
                    }
                } else {
                    $errors[] = 'Email o password non validi';
                }
            } catch (PDOException $e) {
                $errors[] = 'Errore durante il login: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-running"></i> <?php echo SITE_NAME; ?></h1>
                <h2>Accedi al tuo account</h2>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo h($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo h($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" aria-label="Mostra password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group checkbox">
                    <label>
                        <input type="checkbox" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                        Ricordami
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Accedi</button>
                </div>
                
                <div class="auth-links">
                    <p><a href="forgot_password.php">Password dimenticata?</a></p>
                    <p>Non hai un account? <a href="register.php">Registrati</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Script per mostrare/nascondere la password
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Cambia l'icona
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
