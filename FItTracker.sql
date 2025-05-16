-- Script di creazione database per FitTracker
CREATE DATABASE IF NOT EXISTS fittracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fittracker;

-- Tabella utenti
CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    data_nascita DATE NULL,
    sesso ENUM('M', 'F', 'Altro') NULL,
    peso DECIMAL(5,2) NULL COMMENT 'Peso in kg',
    altezza INT NULL COMMENT 'Altezza in cm',
    profilo_img VARCHAR(255) NULL,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_accesso TIMESTAMP NULL,
    remember_token VARCHAR(255) NULL,
    token_expiry TIMESTAMP NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expiry TIMESTAMP NULL,
    preferenze JSON NULL COMMENT 'Preferenze utente in formato JSON'
);

-- Tabella tipi_attivita
CREATE TABLE IF NOT EXISTS tipi_attivita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    icona VARCHAR(50) NULL COMMENT 'Nome classe icona FontAwesome',
    colore VARCHAR(20) NULL COMMENT 'Colore associato al tipo attività (HEX)',
    categoria ENUM('Corsa', 'Ciclismo', 'Nuoto', 'Camminata', 'Fitness', 'Altro') NOT NULL
);

-- Tabella attivita
CREATE TABLE IF NOT EXISTS attivita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    tipo_attivita_id INT NOT NULL,
    titolo VARCHAR(100) NOT NULL,
    descrizione TEXT NULL,
    data_inizio DATETIME NOT NULL,
    data_fine DATETIME NOT NULL,
    durata INT NOT NULL COMMENT 'Durata in secondi',
    distanza DECIMAL(10,2) NULL COMMENT 'Distanza in km',
    calorie INT NULL COMMENT 'Calorie stimate bruciate',
    frequenza_media INT NULL COMMENT 'Frequenza cardiaca media',
    frequenza_max INT NULL COMMENT 'Frequenza cardiaca massima',
    passo_medio VARCHAR(10) NULL COMMENT 'Passo medio (min/km)',
    velocita_media DECIMAL(5,2) NULL COMMENT 'Velocità media (km/h)',
    velocita_max DECIMAL(5,2) NULL COMMENT 'Velocità massima (km/h)',
    dislivello_positivo INT NULL COMMENT 'Dislivello positivo in metri',
    altitudine_max INT NULL COMMENT 'Altitudine massima in metri',
    temperatura DECIMAL(4,1) NULL COMMENT 'Temperatura durante l\'attività',
    condizioni_meteo VARCHAR(50) NULL,
    percorso_gpx TEXT NULL COMMENT 'Percorso GPX dell\'attività',
    dati_json JSON NULL COMMENT 'Dati aggiuntivi in formato JSON',
    percorso_immagine VARCHAR(255) NULL COMMENT 'Percorso immagine associata all\'attività',
    privata BOOLEAN DEFAULT FALSE COMMENT 'Se true, l\'attività è visibile solo all\'utente',
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_attivita_id) REFERENCES tipi_attivita(id)
);

-- Tabella eventi_calendario
CREATE TABLE IF NOT EXISTS eventi_calendario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    titolo VARCHAR(100) NOT NULL,
    tipo_attivita_id INT NULL,
    descrizione TEXT NULL,
    data_inizio DATETIME NOT NULL,
    data_fine DATETIME NOT NULL,
    tutto_il_giorno BOOLEAN DEFAULT FALSE,
    ricorrenza VARCHAR(50) NULL COMMENT 'Pattern di ricorrenza (es. giornaliero, settimanale)',
    ricorrenza_fine DATE NULL COMMENT 'Data fine ricorrenza',
    notifica_minuti INT NULL COMMENT 'Minuti prima per la notifica',
    colore VARCHAR(20) NULL COMMENT 'Colore personalizzato evento',
    completato BOOLEAN DEFAULT FALSE,
    attivita_id INT NULL COMMENT 'ID attività associata se completata',
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_attivita_id) REFERENCES tipi_attivita(id),
    FOREIGN KEY (attivita_id) REFERENCES attivita(id) ON DELETE SET NULL
);

-- Tabella obiettivi
CREATE TABLE IF NOT EXISTS obiettivi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    titolo VARCHAR(100) NOT NULL,
    descrizione TEXT NULL,
    tipo ENUM('Distanza', 'Tempo', 'Calorie', 'Frequenza', 'Personalizzato') NOT NULL,
    valore_target DECIMAL(10,2) NOT NULL COMMENT 'Valore obiettivo',
    unita VARCHAR(20) NOT NULL COMMENT 'Unità di misura',
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    tipo_attivita_id INT NULL COMMENT 'Se null, si applica a tutte le attività',
    progresso DECIMAL(10,2) DEFAULT 0 COMMENT 'Progresso attuale',
    completato BOOLEAN DEFAULT FALSE,
    colore VARCHAR(20) NULL,
    notifiche BOOLEAN DEFAULT TRUE,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_attivita_id) REFERENCES tipi_attivita(id)
);

-- Tabella metriche_salute
CREATE TABLE IF NOT EXISTS metriche_salute (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    data DATE NOT NULL,
    peso DECIMAL(5,2) NULL COMMENT 'Peso in kg',
    percentuale_grasso DECIMAL(4,1) NULL COMMENT 'Percentuale grasso corporeo',
    frequenza_riposo INT NULL COMMENT 'Frequenza cardiaca a riposo',
    passi INT NULL COMMENT 'Numero di passi',
    calorie_attive INT NULL COMMENT 'Calorie attive',
    calorie_totali INT NULL COMMENT 'Calorie totali',
    ore_sonno DECIMAL(4,2) NULL COMMENT 'Ore di sonno',
    qualita_sonno INT NULL COMMENT 'Qualità sonno (1-10)',
    livello_stress INT NULL COMMENT 'Livello di stress (1-10)',
    pressione_sistolica INT NULL,
    pressione_diastolica INT NULL,
    note TEXT NULL,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    UNIQUE KEY (utente_id, data) COMMENT 'Una sola entry per giorno per utente'
);

-- Tabella zone_frequenza
CREATE TABLE IF NOT EXISTS zone_frequenza (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    nome VARCHAR(50) NOT NULL COMMENT 'Es. "Recupero", "Aerobica", "Soglia", "Massimale"',
    frequenza_min INT NOT NULL,
    frequenza_max INT NOT NULL,
    colore VARCHAR(20) NOT NULL,
    descrizione VARCHAR(255) NULL,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
);

-- Tabella percorsi_preferiti
CREATE TABLE IF NOT EXISTS percorsi_preferiti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT NULL,
    tipo_attivita_id INT NOT NULL,
    distanza DECIMAL(10,2) NOT NULL COMMENT 'Distanza in km',
    dislivello INT NULL COMMENT 'Dislivello in metri',
    percorso_gpx TEXT NOT NULL COMMENT 'Percorso GPX',
    punti_interesse JSON NULL COMMENT 'Punti di interesse lungo il percorso',
    immagine VARCHAR(255) NULL,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_attivita_id) REFERENCES tipi_attivita(id)
);

-- Tabella sfide
CREATE TABLE IF NOT EXISTS sfide (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creatore_id INT NOT NULL COMMENT 'Utente che ha creato la sfida',
    titolo VARCHAR(100) NOT NULL,
    descrizione TEXT NULL,
    tipo ENUM('Distanza', 'Tempo', 'Calorie', 'Attività') NOT NULL,
    valore_target DECIMAL(10,2) NOT NULL,
    unita VARCHAR(20) NOT NULL,
    tipo_attivita_id INT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    privata BOOLEAN DEFAULT FALSE,
    codice_accesso VARCHAR(20) NULL COMMENT 'Codice per partecipare se privata',
    immagine VARCHAR(255) NULL,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creatore_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_attivita_id) REFERENCES tipi_attivita(id)
);

-- Tabella partecipanti_sfida
CREATE TABLE IF NOT EXISTS partecipanti_sfida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sfida_id INT NOT NULL,
    utente_id INT NOT NULL,
    data_iscrizione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progresso DECIMAL(10,2) DEFAULT 0,
    completato BOOLEAN DEFAULT FALSE,
    data_completamento TIMESTAMP NULL,
    FOREIGN KEY (sfida_id) REFERENCES sfide(id) ON DELETE CASCADE,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    UNIQUE KEY (sfida_id, utente_id) COMMENT 'Un utente può partecipare a una sfida una sola volta'
);

-- Tabella notifiche
CREATE TABLE IF NOT EXISTS notifiche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo di notifica',
    titolo VARCHAR(100) NOT NULL,
    messaggio TEXT NOT NULL,
    data_notifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    letta BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) NULL COMMENT 'Link relativo alla notifica',
    data_lettura TIMESTAMP NULL,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
);

-- Tabella dispositivi
CREATE TABLE IF NOT EXISTS dispositivi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo dispositivo (es. "Smartwatch", "Fascia cardio")',
    marca VARCHAR(50) NULL,
    modello VARCHAR(50) NULL,
    data_aggiunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_sincronizzato TIMESTAMP NULL,
    token_sync VARCHAR(255) NULL,
    attivo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
);

-- Tabella amicizie
CREATE TABLE IF NOT EXISTS amicizie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    amico_id INT NOT NULL,
    stato ENUM('Pendente', 'Accettata', 'Rifiutata', 'Bloccata') NOT NULL DEFAULT 'Pendente',
    data_richiesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (amico_id) REFERENCES utenti(id) ON DELETE CASCADE,
    UNIQUE KEY (utente_id, amico_id) COMMENT 'Impedisce richieste duplicate'
);

-- Tabella commenti_attivita
CREATE TABLE IF NOT EXISTS commenti_attivita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attivita_id INT NOT NULL,
    utente_id INT NOT NULL,
    commento TEXT NOT NULL,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (attivita_id) REFERENCES attivita(id) ON DELETE CASCADE,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
);

-- Inserimento dati iniziali per tipi_attivita
INSERT INTO tipi_attivita (nome, icona, colore, categoria) VALUES
('Corsa', 'fa-running', '#FF5733', 'Corsa'),
('Ciclismo', 'fa-bicycle', '#33FF57', 'Ciclismo'),
('Nuoto', 'fa-swimmer', '#3357FF', 'Nuoto'),
('Camminata', 'fa-walking', '#FF33A8', 'Camminata'),
('Yoga', 'fa-om', '#A833FF', 'Fitness'),
('Palestra', 'fa-dumbbell', '#33FFF6', 'Fitness'),
('Escursionismo', 'fa-hiking', '#7CFF33', 'Camminata'),
('Mountain Bike', 'fa-mountain', '#FF8C33', 'Ciclismo'),
('Fitness', 'fa-heartbeat', '#FF3333', 'Fitness'),
('Triathlon', 'fa-medal', '#3380FF', 'Altro');
