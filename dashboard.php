<?php
// dashboard.php - Pannello di controllo principale
require_once 'config.php';

// Verifica se l'utente è loggato, altrimenti reindirizza al login
if (!isUserLoggedIn()) {
    redirect('login.php');
}

// Recupera le informazioni dell'utente corrente
$user = getCurrentUser();

// Recupera le attività recenti dell'utente (ultime 5)
$conn = getDbConnection();
$stmt = $conn->prepare("
    SELECT a.*, t.nome as tipo_nome, t.icona, t.colore 
    FROM attivita a 
    JOIN tipi_attivita t ON a.tipo_attivita_id = t.id 
    WHERE a.utente_id = ? 
    ORDER BY a.data_inizio DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$attivita_recenti = $stmt->fetchAll();

// Recupera le statistiche dell'utente
// Totale attività
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM attivita WHERE utente_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_activities = $stmt->fetch()['total'];

// Totale distanza
$stmt = $conn->prepare("SELECT SUM(distanza) as total FROM attivita WHERE utente_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_distance = $stmt->fetch()['total'] ?? 0;

// Totale tempo
$stmt = $conn->prepare("SELECT SUM(durata) as total FROM attivita WHERE utente_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_duration = $stmt->fetch()['total'] ?? 0;

// Totale calorie
$stmt = $conn->prepare("SELECT SUM(calorie) as total FROM attivita WHERE utente_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_calories = $stmt->fetch()['total'] ?? 0;

// Formatta il tempo totale in ore:minuti:secondi
$hours = floor($total_duration / 3600);
$minutes = floor(($total_duration % 3600) / 60);
$seconds = $total_duration % 60;
$formatted_duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

// Recupera i prossimi eventi dal calendario (prossimi 5 giorni)
$today = date('Y-m-d');
$next_week = date('Y-m-d', strtotime('+5 days'));

$stmt = $conn->prepare("
    SELECT * FROM eventi_calendario 
    WHERE utente_id = ? 
    AND data_inizio BETWEEN ? AND ? 
    AND (attivita_id IS NULL OR completato = 0)
    ORDER BY data_inizio ASC
");
$stmt->execute([$_SESSION['user_id'], $today, $next_week]);
$prossimi_eventi = $stmt->fetchAll();

// Recupera gli obiettivi attivi
$stmt = $conn->prepare("
    SELECT * FROM obiettivi 
    WHERE utente_id = ? 
    AND data_fine >= ? 
    AND completato = 0
    ORDER BY data_fine ASC
");
$stmt->execute([$_SESSION['user_id'], $today]);
$obiettivi_attivi = $stmt->fetchAll();

// Recupera le metriche di salute dell'ultima settimana
$last_week = date('Y-m-d', strtotime('-7 days'));
$stmt = $conn->prepare("
    SELECT * FROM metriche_salute 
    WHERE utente_id = ? 
    AND data BETWEEN ? AND ? 
    ORDER BY data ASC
");
$stmt->execute([$_SESSION['user_id'], $last_week, $today]);
$metriche_salute = $stmt->fetchAll();

// Dati per i grafici (ultimi 7 giorni)
// Preparazione array per JavaScript
$dates = [];
$steps_data = [];
$weight_data = [];

// Riempimento array con giorni degli ultimi 7 giorni
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d/m', strtotime($day));
    $steps_data[$day] = 0;
    $weight_data[$day] = null;
}

// Popolamento con dati reali
foreach ($metriche_salute as $metrica) {
    $day = $metrica['data'];
    if (isset($steps_data[$day])) {
        $steps_data[$day] = $metrica['passi'] ?? 0;
    }
    if ($metrica['peso'] !== null) {
        $weight_data[$day] = $metrica['peso'];
    }
}

// Converti in array per JavaScript
$steps_array = array_values($steps_data);
$weight_array = array_values($weight_data);

// Recupera notifiche non lette
$stmt = $conn->prepare("
    SELECT COUNT(*) as total FROM notifiche 
    WHERE utente_id = ? AND letta = 0
");
$stmt->execute([$_SESSION['user_id']]);
$unread_notifications = $stmt->fetch()['total'];

// Funzione per formattare i minuti in ore:minuti
function formatMinutes($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf("%dh %02dm", $hours, $mins);
}

// Funzione per formattare la data in formato leggibile
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Funzione per calcolare la percentuale di completamento di un obiettivo
function calculateProgress($current, $target) {
    if ($target == 0) return 0;
    $progress = ($current / $target) * 100;
    return min(100, $progress);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="dashboard-body">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="dashboard-welcome">
            <h1>Benvenuto, <?php echo h($user['nome']); ?>!</h1>
            <p class="date-today"><?php echo date('l, j F Y'); ?></p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-info">
                    <h3>Attività</h3>
                    <p class="stat-value"><?php echo $total_activities; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-info">
                    <h3>Distanza</h3>
                    <p class="stat-value"><?php echo number_format($total_distance, 1); ?> km</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Tempo</h3>
                    <p class="stat-value"><?php echo $formatted_duration; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-fire-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Calorie</h3>
                    <p class="stat-value"><?php echo number_format($total_calories); ?></p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <!-- Attività recenti -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Attività Recenti</h2>
                    <a href="attivita.php" class="view-all">Vedi tutte <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-content">
                    <?php if (count($attivita_recenti) > 0): ?>
                        <ul class="activities-list">
                            <?php foreach ($attivita_recenti as $attivita): ?>
                                <li class="activity-item">
                                    <div class="activity-icon" style="background-color: <?php echo h($attivita['colore']); ?>">
                                        <i class="fas <?php echo h($attivita['icona']); ?>"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h3><a href="attivita_dettaglio.php?id=<?php echo $attivita['id']; ?>"><?php echo h($attivita['titolo']); ?></a></h3>
                                        <div class="activity-meta">
                                            <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($attivita['data_inizio'])); ?></span>
                                            <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($attivita['data_inizio'])); ?></span>
                                            <?php if($attivita['distanza']): ?>
                                                <span><i class="fas fa-route"></i> <?php echo number_format($attivita['distanza'], 1); ?> km</span>
                                            <?php endif; ?>
                                            <?php if($attivita['durata']): ?>
                                                <span><i class="fas fa-stopwatch"></i> <?php 
                                                    $h = floor($attivita['durata'] / 3600);
                                                    $m = floor(($attivita['durata'] % 3600) / 60);
                                                    echo sprintf("%02d:%02d", $h, $m); 
                                                ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="add-new-btn">
                            <a href="nuova_attivita.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nuova Attività</a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-running"></i>
                            <p>Non hai ancora registrato nessuna attività</p>
                            <a href="nuova_attivita.php" class="btn btn-primary"><i class="fas fa-plus"></i> Registra la tua prima attività</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Calendario -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Prossimi Eventi</h2>
                    <a href="calendario.php" class="view-all">Vedi calendario <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-content">
                    <?php if (count($prossimi_eventi) > 0): ?>
                        <ul class="events-list">
                            <?php foreach ($prossimi_eventi as $evento): ?>
                                <li class="event-item">
                                    <div class="event-date">
                                        <span class="event-day"><?php echo date('d', strtotime($evento['data_inizio'])); ?></span>
                                        <span class="event-month"><?php echo date('M', strtotime($evento['data_inizio'])); ?></span>
                                    </div>
                                    <div class="event-info">
                                        <h3><?php echo h($evento['titolo']); ?></h3>
                                        <div class="event-meta">
                                            <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($evento['data_inizio'])); ?></span>
                                            <?php if (!empty($evento['descrizione'])): ?>
                                                <p class="event-description"><?php echo h($evento['descrizione']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-plus"></i>
                            <p>Non hai eventi pianificati per i prossimi giorni</p>
                            <a href="calendario.php" class="btn btn-primary"><i class="fas fa-plus"></i> Aggiungi un evento</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Grafici -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h2>Statistiche Settimanali</h2>
                </div>
                <div class="card-content charts-container">
                    <div class="chart-wrapper">
                        <canvas id="stepsChart"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="weightChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Obiettivi -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Obiettivi Attivi</h2>
                    <a href="obiettivi.php" class="view-all">Gestisci obiettivi <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-content">
                    <?php if (count($obiettivi_attivi) > 0): ?>
                        <ul class="goals-list">
                            <?php foreach ($obiettivi_attivi as $obiettivo): ?>
                                <?php 
                                    $progress = calculateProgress($obiettivo['progresso'], $obiettivo['valore_target']);
                                    $progress_class = $progress >= 100 ? 'complete' : '';
                                ?>
                                <li class="goal-item">
                                    <div class="goal-info">
                                        <h3><?php echo h($obiettivo['titolo']); ?></h3>
                                        <div class="goal-meta">
                                            <span><i class="fas fa-bullseye"></i> <?php echo h($obiettivo['valore_target'] . ' ' . $obiettivo['unita']); ?></span>
                                            <span><i class="fas fa-calendar-alt"></i> Scadenza: <?php echo formatDate($obiettivo['data_fine']); ?></span>
                                        </div>
                                    </div>
                                    <div class="goal-progress">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar <?php echo $progress_class; ?>" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo round($progress); ?>%</span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bullseye"></i>
                            <p>Non hai obiettivi attivi</p>
                            <a href="obiettivi.php" class="btn btn-primary"><i class="fas fa-plus"></i> Crea un obiettivo</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Metriche di Salute -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Salute</h2>
                    <a href="salute.php" class="view-all">Vedi tutto <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-content">
                    <div class="health-metrics">
                        <?php 
                        // Recupera i dati più recenti se esistono
                        $last_metrics = !empty($metriche_salute) ? end($metriche_salute) : null;
                        ?>
                        
                        <?php if ($last_metrics): ?>
                            <div class="health-metric-card">
                                <div class="metric-icon">
                                    <i class="fas fa-weight"></i>
                                </div>
                                <div class="metric-data">
                                    <h3>Peso</h3>
                                    <p class="metric-value"><?php echo $last_metrics['peso'] ? number_format($last_metrics['peso'], 1) . ' kg' : 'N/D'; ?></p>
                                </div>
                            </div>
                            
                            <div class="health-metric-card">
                                <div class="metric-icon">
                                    <i class="fas fa-shoe-prints"></i>
                                </div>
                                <div class="metric-data">
                                    <h3>Passi</h3>
                                    <p class="metric-value"><?php echo $last_metrics['passi'] ? number_format($last_metrics['passi']) : 'N/D'; ?></p>
                                </div>
                            </div>
                            
                            <div class="health-metric-card">
                                <div class="metric-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="metric-data">
                                    <h3>Freq. Cardiaca</h3>
                                    <p class="metric-value"><?php echo $last_metrics['frequenza_riposo'] ? $last_metrics['frequenza_riposo'] . ' bpm' : 'N/D'; ?></p>
                                </div>
                            </div>
                            
                            <div class="health-metric-card">
                                <div class="metric-icon">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <div class="metric-data">
                                    <h3>Sonno</h3>
                                    <p class="metric-value"><?php echo $last_metrics['ore_sonno'] ? number_format($last_metrics['ore_sonno'], 1) . ' ore' : 'N/D'; ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-heartbeat"></i>
                                <p>Non hai ancora registrato dati sulla salute</p>
                                <a href="salute.php" class="btn btn-primary"><i class="fas fa-plus"></i> Aggiungi dati</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Configurazione grafici
        document.addEventListener('DOMContentLoaded', function() {
            // Grafico passi
            const stepsCtx = document.getElementById('stepsChart').getContext('2d');
            const stepsChart = new Chart(stepsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [{
                        label: 'Passi giornalieri',
                        data: <?php echo json_encode($steps_array); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Passi Giornalieri'
                        }
                    }
                }
            });
            
            // Grafico peso
            const weightCtx = document.getElementById('weightChart').getContext('2d');
            const weightChart = new Chart(weightCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [{
                        label: 'Peso (kg)',
                        data: <?php echo json_encode($weight_array); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Andamento Peso'
                        }
                    }
                }
            });
        });
    </script>

</body>
</html>