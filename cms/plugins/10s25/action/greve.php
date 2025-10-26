<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

define('_GREVE_FILE', _DIR_TMP . 'greve.txt');

function action_greve_dist() {
    include_spip('inc/cookie'); // pour manipuler les cookies

    // Vérifier si la personne est déjà comptée (via cookie)
    if (isset($_COOKIE['greviste'])) {
        include_spip('inc/headers');
        redirige_par_entete(parametre_url(self(), 'deja', 1, '&'));
        return;
    }

    $f = _GREVE_FILE;
    $nb = 0;

    if (file_exists($f)) {
        $nb = intval(@file_get_contents($f));
    }

    $nb++;
    @file_put_contents($f, $nb);

    // On pose un cookie valable 1 an
    spip_setcookie('greviste', 1, time() + 365 * 24 * 3600);

    // Redirection après l’action
    include_spip('inc/headers');
    redirige_par_entete(parametre_url(self(), 'ok', 1, '&'));
}
