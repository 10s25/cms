<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Action maj_geolieu : modifie une rubrique
 * Appelée depuis #URL_ACTION_AUTEUR{maj_geolieu, id_rubrique, redirect}
 */
function action_maj_geolieu_dist(){
  // Récupérer l'arg sécurisé (ID du site) depuis l'URL d'action
  $redirect = _request('redirect');
  $securiser_action = charger_fonction('securiser_action', 'inc');
  $id_rubrique = intval($securiser_action());

  include_spip('inc/autoriser');
  if (!autoriser('modifier', 'rubrique', $id_rubrique)) {
    $redirect = parametre_url($redirect, 'err', 'manque_droits');
    include_spip('inc/headers');
    redirige_par_entete($redirect);
    exit;
  }

  // Récupérer les champs postés
  $nom = trim((string)_request('nom_lieu'));
  $nom = strip_tags($nom);               // on nettoie

  // Mettre à jour l'objet site
  include_spip('action/editer_objet');
  $err = objet_modifier('rubrique', $id_rubrique, array(
    'titre'   => $nom,
  ));

  // Redirection finale
  $redirect = parametre_url($redirect, 'maj', 'ok');
  include_spip('inc/headers');
  redirige_par_entete($redirect); // fourni par #URL_ACTION_AUTEUR (ici #SELF)
}
