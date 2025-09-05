<?php

// pour debug
// ini_set('display_errors', 'On');
// false en développement, true en prod
$PROD = false;
// jeton secret à modifier en prod
$access_token = 'accesstoken'; 
// mettre la liste d'adresses autorisées en prod
$allowed_ips = ['192.168.0.1', '127.0.0.1', '172.18.0.1'];
// chemin des dumps
$backup_dir = 'tmp/dump/';

// -----

// Empêche l'accès direct via l'URL en prod
if ($PROD && isset($_SERVER['SCRIPT_FILENAME']) && $_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    http_response_code(403);
    die('Accès interdit depuis l\'url.');
}

// Vérifie l'adresse IP
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    http_response_code(403);
    die('Accès non autorisé depuis cette IP : ' . $_SERVER['REMOTE_ADDR'] );
}

// Vérifie le jeton
if (!isset($_GET['token']) || $_GET['token'] !== $access_token) {
    http_response_code(403);
    die('Accès interdit au jeton.');
}

// Vérifie existence dossier
if (!is_dir($backup_dir)) {
    http_response_code(404);
    die('Dossier non trouvé : ' . $backup_dir);
}

// Recherche fichier 
$latest_file = null;
$latest_mtime = 0;
$files = scandir($backup_dir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }
    $filepath = $backup_dir . $file;
    // Filtre uniquement les .sqlite
    if (pathinfo($filepath, PATHINFO_EXTENSION) === 'sqlite') {
        // garde uniquement le fichier le plus récent
        // (permet sauvegarde manuelle en plus de automatique)
        $mtime = filemtime($filepath);
        if ($mtime > $latest_mtime) {
            $latest_mtime = $mtime;
            $latest_file = $filepath;
        }
    }
}

// Vérifie existence fichier
if (!$latest_file || !file_exists($latest_file)) {
    http_response_code(404);
    die('Aucune sauvegarde trouvée.');
}

// Envoie le fichier
header('Content-Disposition: attachment; filename="' . basename($latest_file) . '"');
header('Content-Length: ' . filesize($latest_file));
readfile($latest_file);

exit;
?>