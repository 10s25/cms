<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function action_maj_geolien_dist(){
  // Récupérer l'arg sécurisé (ID du site) depuis l'URL d'action
  $securiser_action = charger_fonction('securiser_action', 'inc');
  $id_syndic = intval($securiser_action());

  include_spip('inc/autoriser');
  if (!autoriser('modifier', 'site', $id_syndic)) {
    include_spip('inc/headers');
    redirige_par_entete(_request('redirect'));
    exit;
  }

  // Données postées
  $nom_site = trim(strip_tags((string)_request('nom_site')));
  $url_site = trim((string)_request('url_site'));

  $lat_raw = trim((string)_request('lat_site'));
  $lon_raw = trim((string)_request('long_site'));

  // Mots-clés
  $id_mot_territoire = intval(_request('territoire_site')); // groupe 2
  $id_mot_typesite   = intval(_request('typesite_site'));   // groupe 4

  // ---------- VALIDATION ----------
  // helper numérique
  $toFloat = function($s){
    if ($s === '' || $s === null) return null;
    $s = str_replace(',', '.', (string)$s);
    return (is_numeric($s) ? (float)$s : null);
  };
  $lat = $toFloat($lat_raw);
  $lon = $toFloat($lon_raw);

  if(empty($url_site)) {
    // On renvoie une erreur ciblée ; la page pourra l'afficher
    $redirect = parametre_url($redirect, 'err', 'manque_url_site');
    include_spip('inc/headers');
    redirige_par_entete($redirect);
    exit;
  }

  if(empty($nom_site)) {
    // On renvoie une erreur ciblée ; la page pourra l'afficher
    $redirect = parametre_url($redirect, 'err', 'manque_nom_site');
    include_spip('inc/headers');
    redirige_par_entete($redirect);
    exit;
  }

  if ($id_mot_territoire) {
    // Lat/lon obligatoires
    $coords_exists = $lat !== null && $lon !== null;
    if (!$coords_exists) {
      include_spip('inc/filtres'); // parametre_url()
      // On renvoie une erreur ciblée ; la page pourra l'afficher
      $redirect = parametre_url($redirect, 'err', 'manque_coords');
      include_spip('inc/headers');
      redirige_par_entete($redirect);
      exit;
    }
  } else {
    // Si pas de territoire, lat/lon interdits
    if ($lat !== null || $lon !== null) {
      // On renvoie une erreur ciblée ; la page pourra l'afficher
      $redirect = parametre_url($redirect, 'err', 'coords_interdits');
      include_spip('inc/headers');
      redirige_par_entete($redirect);
      exit;
    }
  }

  if ($lat !== null) {
      if ($lon === null) {
        // lat et lon doivent exister tous les deux
        include_spip('inc/filtres'); // parametre_url()
        // On renvoie une erreur ciblée ; la page pourra l'afficher
        $redirect = parametre_url($redirect, 'err', 'manque_longitude');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
      }
      if (!($lat >= -90 && $lat <= 90)) {
        // Lat doit etre dans les bornes
        // On renvoie une erreur ciblée ; la page pourra l'afficher
        $redirect = parametre_url($redirect, 'err', 'invalide_latitude');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
      }
  }

  if ($lon !== null) {
      if ($lat === null) {
        // lat et lon doivent exister tous les deux
        include_spip('inc/filtres'); // parametre_url()
        // On renvoie une erreur ciblée ; la page pourra l'afficher
        $redirect = parametre_url($redirect, 'err', 'manque_latitude');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
      }
      if (!($lon >= -180 && $lon <= 180)) {
        // Lat doit etre dans les bornes
        // On renvoie une erreur ciblée ; la page pourra l'afficher
        $redirect = parametre_url($redirect, 'err', 'invalide_longitude');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
      }
  }
  // ---------------------------------------------------------------

  // Construire le descriptif "lat, lon" (optionnel si pas de territoire)
  $descriptif = '';
  if ($lat !== null && $lon !== null) {
    $descriptif = trim(($lat !== null ? $lat : '')).( $lat!==null ? ', ' : '' ).trim(($lon !== null ? $lon : ''));
  }

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

  $reset_associer(2, $id_mot_territoire); // groupe 2
  $reset_associer(4, $id_mot_typesite);   // groupe 4

  // Redirection finale
  include_spip('inc/headers');
  redirige_par_entete(_request('redirect')); // fourni par #URL_ACTION_AUTEUR (ici #SELF avec flags)
}
