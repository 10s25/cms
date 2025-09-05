<?php

// false en développement, true en prod
$PROD = false;
// jeton secret à modifier en prod
$access_token = 'accesstoken'; 
// mettre la liste d'adresses autorisées en prod
$allowed_ips = ['192.168.0.1', '127.0.0.1', '172.18.0.1'];
// chemin de la base de données
$db_file = 'tmp/dump/Mon_site_SPIP_20250905.sqlite';

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

// Vérifie l'existance du fichier
if (!file_exists($db_file)) {
    http_response_code(404);
    die('Fichier introuvable.');
}

// Envoie le fichier
header('Content-Disposition: attachment; filename="database.sqlite"');
header('Content-Length: ' . filesize($db_file));
readfile($db_file);

exit;
?>