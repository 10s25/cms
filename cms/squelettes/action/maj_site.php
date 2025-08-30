<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function action_maj_site_dist(){
  // Récupérer l'arg sécurisé (ID du site) depuis l'URL d'action
  $securiser_action = charger_fonction('securiser_action', 'inc');
  $id_syndic = intval($securiser_action());

  include_spip('inc/autoriser');
  if (!autoriser('modifier', 'site', $id_syndic)) {
    include_spip('inc/headers');
    redirige_par_entete(_request('redirect'));
    exit;
  }

  // Récupérer les champs postés
  $nom_site = _request('nom_site');
  $url_site = _request('url_site');
  $lat      = trim((string) _request('lat_site'));
  $lon      = trim((string) _request('long_site'));

  // Normaliser lat/lon (virgules -> points), et reconstruire le descriptif "lat, lon"
  $lat = str_replace(',', '.', $lat);
  $lon = str_replace(',', '.', $lon);
  $descriptif = trim($lat) . (strlen($lat) ? ', ' : '') . trim($lon);

  // Mettre à jour l'objet site
  include_spip('action/editer_objet');
  $err = objet_modifier('site', $id_syndic, array(
    'nom_site'   => $nom_site,
    'url_site'   => $url_site,
    'descriptif' => $descriptif,
  ));

  // Gérer les mots-clés (0..1 par groupe)
  include_spip('action/editer_liens');
  include_spip('base/abstract_sql');

  // Helper pour reset/associer un groupe de mots
  $reset_associer = function($id_groupe, $id_mot_nouveau) use ($id_syndic) {
    // dissocier tous les mots du groupe pour ce site
    $ids = array_map('reset', sql_allfetsel(
      'm.id_mot',
      'spip_mots_liens AS ml JOIN spip_mots AS m ON m.id_mot=ml.id_mot',
      'ml.objet='.sql_quote('site').' AND ml.id_objet='.intval($id_syndic).' AND m.id_groupe='.intval($id_groupe)
    ));
    if ($ids) {
      objet_dissocier(array('mot'=>$ids), array('site'=>$id_syndic));
    }
    // associer le nouveau s'il est fourni
    if (intval($id_mot_nouveau)) {
      objet_associer(array('mot'=>intval($id_mot_nouveau)), array('site'=>$id_syndic));
    }
  };

  $reset_associer(2, _request('territoire_site')); // groupe 2
  $reset_associer(4, _request('typesite_site'));   // groupe 4

  // Redirection finale
  include_spip('inc/headers');
  redirige_par_entete(_request('redirect')); // fourni par #URL_ACTION_AUTEUR (ici #SELF avec flags)
}
