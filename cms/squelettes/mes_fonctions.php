<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

// Fichier où l'on stocke le compteur
define('_GREVE_FILE', _DIR_TMP . 'greve.txt');

/**
 * Balise SPIP : [(#GREVISTES)]
 */
function balise_GREVISTES_dist($p) {
    $p->code = "intval((file_exists(_GREVE_FILE) ? @file_get_contents(_GREVE_FILE) : 0))";
    $p->interdire_scripts = false;
    return $p;
}
