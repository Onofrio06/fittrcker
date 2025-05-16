<?php
// index.php - Pagina principale/Accesso all'applicazione
require_once 'config.php';

// Se l'utente è già loggato, reindirizza alla dashboard
if (isUserLoggedIn()) {
    redirect('dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Monitora le tue attività fisiche</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="landing-container">
        <header class="landing-header">
            <div class="logo">
                <h1><i class="fas fa-running"></i> <?php echo SITE_NAME; ?></h1>
            </div>
            <nav class="landing-nav">
                <ul>
                    <li><a href="#features">Funzionalità</a></li>
                    <li><a href="#how-it-works">Come Funziona</a></li>
                    <li><a href="login.php" class="btn-login">Accedi</a></li>
                    <li><a href="register.php" class="btn-register">Registrati</a></li>
                </ul>
            </nav>
        </header>

        <section class="hero">
            <div class="hero-content">
                <h2>Monitora, Analizza e Migliora le tue Prestazioni Sportive</h2>
                <p>Tieni traccia delle tue attività fisiche, imposta obiettivi, visualizza statistiche e condividi i tuoi progressi con amici.</p>
                <a href="register.php" class="btn-cta">Inizia Subito - È Gratis!</a>
            </div>
            <div class="hero-image">
                <img src="img/hero-dashboard.png" alt="Dashboard FitTracker">
            </div>
        </section>

        <section id="features" class="features">
            <h2>Funzionalità Principali</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Analisi Dettagliate</h3>
                    <p>Visualizza grafici e statistiche delle tue attività per monitorare i progressi nel tempo.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Calendario Attività</h3>
                    <p>Pianifica i tuoi allenamenti e visualizza facilmente la cronologia delle tue attività.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bullseye"></i>
                    <h3>Obiettivi Personalizzati</h3>
                    <p>Imposta obiettivi di distanza, tempo o calorie e monitora i tuoi progressi.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Comunità e Sfide</h3>
                    <p>Partecipa a sfide con amici e condividi i tuoi successi.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-heartbeat"></i>
                    <h3>Monitoraggio Salute</h3>
                    <p>Registra frequenza cardiaca, passi giornalieri e altre metriche di salute.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Responsive Design</h3>
                    <p>Accedi da qualsiasi dispositivo, FitTracker si adatta al tuo schermo.</p>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="how-it-works">
            <h2>Come Funziona</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Registrati</h3>
                    <p>Crea un account gratuito in pochi secondi.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Registra le Attività</h3>
                    <p>Aggiungi manualmente le tue attività o importa dati da altri dispositivi.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Analizza i Dati</h3>
                    <p>Visualizza statistiche dettagliate e monitora i progressi.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Migliora</h3>
                    <p>Usa i dati per ottimizzare i tuoi allenamenti e raggiungere i tuoi obiettivi.</p>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <h2>Cosa Dicono gli Utenti</h2>
            <div class="testimonial-slider">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"FitTracker ha rivoluzionato il mio approccio all'allenamento. Ora posso vedere chiaramente i miei progressi e pianificare meglio le mie sessioni."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="img/user1.jpg" alt="Marco B.">
                        <div>
                            <h4>Marco B.</h4>
                            <p>Runner</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"La funzione calendario è fantastica! Posso pianificare i miei allenamenti e ricevere notifiche per non dimenticarli."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="img/user2.jpg" alt="Laura M.">
                        <div>
                            <h4>Laura M.</h4>
                            <p>Ciclista</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"Le sfide con gli amici mi mantengono motivato. Adoro la competizione amichevole che FitTracker permette di creare."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="img/user3.jpg" alt="Alessandro G.">
                        <div>
                            <h4>Alessandro G.</h4>
                            <p>Triatleta</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta">
            <h2>Pronto a Iniziare?</h2>
            <p>Unisciti a migliaia di appassionati di fitness che utilizzano FitTracker per monitorare e migliorare le loro prestazioni.</p>
            <a href="register.php" class="btn-cta">Registrati Ora</a>
        </section>

        <footer class="landing-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3><i class="fas fa-running"></i> <?php echo SITE_NAME; ?></h3>
                    <p>La tua piattaforma per il monitoraggio delle attività fisiche</p>
                </div>
                <div class="footer-links">
                    <div class="footer-links-column">
                        <h4>Piattaforma</h4>
                        <ul>
                            <li><a href="#features">Funzionalità</a></li>
                            <li><a href="#how-it-works">Come Funziona</a></li>
                            <li><a href="login.php">Accedi</a></li>
                            <li><a href="register.php">Registrati</a></li>
                        </ul>
                    </div>
                    <div class="footer-links-column">
                        <h4>Risorse</h4>
                        <ul>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Guide</a></li>
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Supporto</a></li>
                        </ul>
                    </div>
                    <div class="footer-links-column">
                        <h4>Legale</h4>
                        <ul>
                            <li><a href="#">Termini di Servizio</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Cookie Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tutti i diritti riservati.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </footer>
    </div>

    <script src="js/main.js"></script>
</body>
</html>