<?php
// config.php - File di configurazione principale
define('DB_HOST', 'localhost');
define('DB_NAME', 'fittracker');
define('DB_USER', 'root');
define('DB_PASS', ''); // In un ambiente di produzione, usa una password sicura

define('SITE_NAME', 'FitTracker');
define('SITE_URL', 'http://localhost/fittracker');
define('DEFAULT_LANG', 'it');

// Impostazioni sessione
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Imposta a 1 se usi HTTPS
session_start();

// Funzione di connessione al database
function getDbConnection() {
    static $conn;
    
    if ($conn === null) {
        try {
            $conn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Errore di connessione al database: ' . $e->getMessage());
        }
    }
    
    return $conn;
}

// Funzione per verificare se l'utente è loggato
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Funzione per reindirizzare a una pagina
function redirect($page) {
    header("Location: " . SITE_URL . "/$page");
    exit;
}

// Funzione per proteggere da XSS
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Funzione per generare un token CSRF
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Funzione per verificare un token CSRF
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('Errore di validazione CSRF');
    }
    return true;
}

// Funzione per recuperare i dati utente
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, nome, cognome, email, data_nascita, sesso, peso, altezza FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    return $stmt->fetch();
}