<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function action_pub_geolien_dist(){
  // Récupérer l'arg sécurisé (ID du site) depuis l'URL d'action
  $redirect = _request('redirect');
  $securiser_action = charger_fonction('securiser_action', 'inc');
  $id_syndic = intval($securiser_action());

  $statut = trim(strip_tags((string)_request('statut')));

  include_spip('inc/autoriser');
  if (!autoriser('publier', 'site', $id_syndic)) {
    $redirect = parametre_url($redirect, 'err', 'manque_droits');
    include_spip('inc/headers');
    redirige_par_entete($redirect);
    exit;
  }

  // Mettre à jour l'objet site
  include_spip('action/editer_objet');
  objet_modifier('site', $id_syndic, array(
    'statut' => $statut,
  ));

  // Redirection finale
  include_spip('inc/headers');
  $redirect = parametre_url($redirect, 'maj', 'ok');
  redirige_par_entete($redirect);
}
