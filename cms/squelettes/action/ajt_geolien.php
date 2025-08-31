<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Action ajt_geolien : créer un site (spip_syndic) dans une rubrique
 * + associer mots-clés éventuels
 * + publier si autorisé
 * + VALIDER que si un territoire est choisi, lat & lon sont renseignées/valides.
 *
 * Appel : #URL_ACTION_AUTEUR{ajt_geolien, id_rubrique_parent, redirect}
 */
function action_ajt_geolien_dist(){

  // Arg sécurisé = id de la rubrique parente
  $securiser_action = charger_fonction('securiser_action', 'inc');
  $id_rubrique = intval($securiser_action());

  $redirect = _request('redirect');

  // Autorisations
  include_spip('inc/autoriser');
  $ok = autoriser('creersitedans', 'rubrique', $id_rubrique);
  if (!$ok) $ok = autoriser('creer', 'site', $id_rubrique);
  if (!$ok || !$id_rubrique) {
    $redirect = parametre_url($redirect, 'err', 'manque_droits');
    include_spip('inc/headers');
    redirige_par_entete($redirect);
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

  if (!$id_mot_typesite) {
    // On renvoie une erreur ciblée ; la page pourra l'afficher
    $redirect = parametre_url($redirect, 'err', 'manque_type_site');
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
      $redirect = parametre_url($redirect, 'err', 'manque_coords_site');
      include_spip('inc/headers');
      redirige_par_entete($redirect);
      exit;
    }
  } else {
    // Si pas de territoire, lat/lon interdits
    if ($lat !== null || $lon !== null) {
      // On renvoie une erreur ciblée ; la page pourra l'afficher
      $redirect = parametre_url($redirect, 'err', 'interdits_coords_site');
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
        $redirect = parametre_url($redirect, 'err', 'manque_longitude_site');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
      }
      if (!($lat >= -90 && $lat <= 90)) {
        // Lat doit etre dans les bornes
        // On renvoie une erreur ciblée ; la page pourra l'afficher
        $redirect = parametre_url($redirect, 'err', 'invalide_latitude_site');
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
        $redirect = parametre_url($redirect, 'err', 'manque_latitude_site');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
      }
      if (!($lon >= -180 && $lon <= 180)) {
        // Lat doit etre dans les bornes
        // On renvoie une erreur ciblée ; la page pourra l'afficher
        $redirect = parametre_url($redirect, 'err', 'invalide_longitude_site');
        include_spip('inc/headers');
        redirige_par_entete($redirect);
        exit;
      }
  }
  // ---------------------------------------------------------------

  // Construire le descriptif "lat, lon" (optionnel si pas de territoire)
  $descriptif = '';
  if ($lat !== null || $lon !== null) {
    $descriptif = trim(($lat !== null ? $lat : '')).( $lat!==null ? ', ' : '' ).trim(($lon !== null ? $lon : ''));
  }

  // Création du site
  include_spip('action/editer_objet');
  $id_syndic = objet_inserer('site', $id_rubrique);
  if ($id_syndic) {
    $set = array(
      'nom_site'   => ($nom_site ?: 'Nouveau site'),
      'url_site'   => $url_site,
      'descriptif' => $descriptif,
    );
    // Publication auto si autorisée
    if (autoriser('publier', 'site', $id_syndic)) {
      $set['statut'] = 'publie';
    }
    objet_modifier('site', $id_syndic, $set);
  }

  // Associer les mots-clés (si fournis)
  if ($id_syndic) {
    include_spip('action/editer_liens');
    if ($id_mot_territoire) {
      objet_associer(array('mot' => $id_mot_territoire), array('site' => $id_syndic));
    }
    if ($id_mot_typesite) {
      objet_associer(array('mot' => $id_mot_typesite), array('site' => $id_syndic));
    }
  }

  // Redirection finale (+ id créé)
  include_spip('inc/filtres'); // parametre_url()
  if ($id_syndic) {
    $redirect = parametre_url($redirect, 'new', $id_syndic);
  }

  $redirect = parametre_url($redirect, 'maj', 'ok');
  include_spip('inc/headers');
  redirige_par_entete($redirect);
}
