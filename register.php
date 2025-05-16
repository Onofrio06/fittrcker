<?php
// register.php - Pagina di registrazione
require_once 'config.php';

// Se l'utente è già loggato, reindirizza alla dashboard
if (isUserLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = false;

// Verifica se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Errore di validazione del form. Riprova.';
    } else {
        // Validazione input
        $nome = trim($_POST['nome'] ?? '');
        $cognome = trim($_POST['cognome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $conferma_password = $_POST['conferma_password'] ?? '';
        $data_nascita = trim($_POST['data_nascita'] ?? '');
        $sesso = trim($_POST['sesso'] ?? '');
        
        // Validazione nome
        if (empty($nome)) {
            $errors[] = 'Il nome è obbligatorio';
        } elseif (strlen($nome) > 50) {
            $errors[] = 'Il nome non può superare i 50 caratteri';
        }
        
        // Validazione cognome
        if (empty($cognome)) {
            $errors[] = 'Il cognome è obbligatorio';
        } elseif (strlen($cognome) > 50) {
            $errors[] = 'Il cognome non può superare i 50 caratteri';
        }
        
        // Validazione email
        if (empty($email)) {
            $errors[] = 'L\'email è obbligatoria';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email non è valida';
        } elseif (strlen($email) > 100) {
            $errors[] = 'L\'email non può superare i 100 caratteri';
        } else {
            // Verifica se l'email è già registrata
            $conn = getDbConnection();
            $stmt = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = 'Questa email è già registrata';
            }
        }
        
        // Validazione password
        if (empty($password)) {
            $errors[] = 'La password è obbligatoria';
        } elseif (strlen($password) < 8) {
            $errors[] = 'La password deve contenere almeno 8 caratteri';
        } elseif ($password !== $conferma_password) {
            $errors[] = 'Le password non corrispondono';
        }
        
        // Se non ci sono errori, procedi con la registrazione
        if (empty($errors)) {
            try {
                $conn = getDbConnection();
                $stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, email, password, data_nascita, sesso) VALUES (?, ?, ?, ?, ?, ?)");
                
                // Hash della password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Se la data di nascita è vuota, imposta NULL
                $data_nascita = !empty($data_nascita) ? $data_nascita : null;
                
                $stmt->execute([$nome, $cognome, $email, $hashed_password, $data_nascita, $sesso]);
                
                $success = true;
                
                // Reindirizza al login dopo 3 secondi
                header("refresh:3;url=login.php");
            } catch (PDOException $e) {
                $errors[] = 'Errore durante la registrazione: ' . $e->getMessage();
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
    <title>Registrazione - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-running"></i> <?php echo SITE_NAME; ?></h1>
                <h2>Crea un account</h2>
            </div>
            
            <?php if ($success): ?>
                <div class="alert success">
                    <p>Registrazione completata con successo! Verrai reindirizzato alla pagina di login.</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo h($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" value="<?php echo h($_POST['nome'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cognome">Cognome *</label>
                        <input type="text" id="cognome" name="cognome" value="<?php echo h($_POST['cognome'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo h($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="toggle-password" aria-label="Mostra password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="conferma_password">Conferma Password *</label>
                        <div class="password-field">
                            <input type="password" id="conferma_password" name="conferma_password" required>
                            <button type="button" class="toggle-password" aria-label="Mostra password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_nascita">Data di nascita</label>
                        <input type="date" id="data_nascita" name="data_nascita" value="<?php echo h($_POST['data_nascita'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="sesso">Sesso</label>
                        <select id="sesso" name="sesso">
                            <option value="" <?php echo empty($_POST['sesso'] ?? '') ? 'selected' : ''; ?>>Seleziona</option>
                            <option value="M" <?php echo isset($_POST['sesso']) && $_POST['sesso'] === 'M' ? 'selected' : ''; ?>>Maschile</option>
                            <option value="F" <?php echo isset($_POST['sesso']) && $_POST['sesso'] === 'F' ? 'selected' : ''; ?>>Femminile</option>
                            <option value="Altro" <?php echo isset($_POST['sesso']) && $_POST['sesso'] === 'Altro' ? 'selected' : ''; ?>>Altro</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Registrati</button>
                </div>
                
                <div class="auth-links">
                    <p>Hai già un account? <a href="login.php">Accedi</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Script per mostrare/nascondere la password
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                // Cambia l'icona
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>