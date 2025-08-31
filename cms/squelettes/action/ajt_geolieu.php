<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Action ajt_geolieu : créer une sous-rubrique dans une rubrique donnée.
 * Appelée depuis #URL_ACTION_AUTEUR{ajt_geolieu, id_parent, redirect}
 */
function action_ajt_geolieu_dist(){

    // 1) Sécuriser et récupérer l'argument (id de la rubrique parente)
  $redirect = _request('redirect');
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $id_parent = intval($securiser_action());

    // 2) Vérifier autorisations
    include_spip('inc/autoriser');
    if (!$id_parent || !autoriser('creer', 'rubrique', $id_parent)) {
        $redirect = parametre_url($redirect, 'err', 'manque_droits');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
    }

    // 3) Récupérer le titre saisi
    $nom = trim((string)_request('nom_lieu'));
    $nom = strip_tags($nom);               // on nettoie
    if ($nom === '') $nom = 'Nouveau lieu';

    // 4) Insérer la sous-rubrique puis la modifier (titre)
    include_spip('action/editer_objet');
    $id_rubrique_new = objet_inserer('rubrique', $id_parent);
    if ($id_rubrique_new) {
        objet_modifier('rubrique', $id_rubrique_new, array(
            'titre' => $nom,
            // 'descriptif' => '...',   // <- si tu veux poser autre chose, fais-le ici
        ));
    }

    // 5) Redirection finale (on ajoute l'id créé en paramètre si besoin)
    include_spip('inc/filtres'); // pour parametre_url()
    if ($id_rubrique_new) {
        $redirect = parametre_url($redirect, 'new', $id_rubrique_new);
    }

    $redirect = parametre_url($redirect, 'maj', 'ok');
    include_spip('inc/headers');
    redirige_par_entete($redirect);
}
