<?php

/**
 * Gestion du formulaire d'ajout d'une doléance'
 *
 * @package 10s25
 **/

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/actions');
include_spip('inc/editer');

/**
 * On autorise tout le monde à créer un nouvel article dans la rubrique Doléances (rub 3)
 *
 * @param string $action
 * @param string $objet
 * @param integer $id_objet
 * @return boolean
 */
function autoriser_rubrique_creerarticledans($action, $objet, $id_objet) {
	if ($action == 'creerarticledans'
		and $objet == 'rubrique'
		and $id_objet ==3) {
		return true;
	}
}

/**
 * On autorise la modification d'un article (requis pour le passer "proposé")
 *
 * @param string $action
 * @param string $objet
 * @param integer $id_objet
 * @return boolean
 */
function autoriser_article_modifier($action, $objet, $id_objet) {
	if ($action == 'modifier'
		and $objet == 'article'
		) {
		return true;
	}
}


/**
 * Chargement du formulaire d'édition d'article
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int|string $id_article
 *     Identifiant de l'article. 'new' pour une nouvel article.
 * @param int $id_rubrique
 *     Identifiant de la rubrique parente
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un article source de traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL de l'article, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Environnement du formulaire
 **/
function formulaires_ajouter_doleance_charger_dist(
	$id_article = 'new',
	$id_rubrique = 0,
	$retour = '',
	$lier_trad = 0,
	$config_fonc = 'articles_edit_config',
	$row = [],
	$hidden = ''
) {
	$valeurs = formulaires_editer_objet_charger(
		'article',
		$id_article,
		$id_rubrique,
		$lier_trad,
		$retour,
		$config_fonc,
		$row,
		$hidden
	);
/*
	if (intval($id_article) and !autoriser('modifier', 'article', intval($id_article))) {
		$valeurs['editable'] = '';
	}
*/
	if ($id_rubrique == 3) {
		$valeurs['editable'] = ' ';
	}
	return $valeurs;
}

/**
 * Identifier le formulaire en faisant abstraction des paramètres qui
 * ne représentent pas l'objet édité
 *
 * @param int|string $id_article
 *     Identifiant de l'article. 'new' pour une nouvel article.
 * @param int $id_rubrique
 *     Identifiant de la rubrique parente
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un article source de traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL de l'article, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return string
 *     Hash du formulaire
 */
function formulaires_ajouter_doleance_identifier_dist(
	$id_article = 'new',
	$id_rubrique = 0,
	$retour = '',
	$lier_trad = 0,
	$config_fonc = 'articles_edit_config',
	$row = [],
	$hidden = ''
) {
	return serialize([intval($id_article), $lier_trad]);
}

/**
 * Choix par défaut des options de présentation
 *
 * @param array $row
 *     Valeurs de la ligne SQL d'un article, si connu
 * return array
 *     Configuration pour le formulaire
 */
function articles_edit_config(array $row): array {

	$config = [];
	$config['lignes'] = 8;
	$config['langue'] = $GLOBALS['spip_lang'];
	$config['restreint'] = ($row['statut'] === 'publie');

	return $config;
}

/**
 * Vérifications du formulaire d'édition d'article
 *
 * @uses formulaires_editer_objet_verifier()
 *
 * @param int|string $id_article
 *     Identifiant de l'article. 'new' pour une nouvel article.
 * @param int $id_rubrique
 *     Identifiant de la rubrique parente
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un article source de traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL de l'article, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Erreurs du formulaire
 **/
function formulaires_ajouter_doleance_verifier_dist(
	$id_article = 'new',
	$id_rubrique = 0,
	$retour = '',
	$lier_trad = 0,
	$config_fonc = 'articles_edit_config',
	$row = [],
	$hidden = ''
) {
	// auto-renseigner le titre si il n'existe pas
	titre_automatique('titre', ['descriptif', 'chapo', 'texte']);
	if (!_request('id_parent') and !intval($id_article)) {
		$valeurs = formulaires_editer_objet_charger(
			'article',
			$id_article,
			$id_rubrique,
			$lier_trad,
			$retour,
			$config_fonc,
			$row,
			$hidden
		);
		set_request('id_parent', $valeurs['id_parent']);
	}
	// on ne demande pas le titre obligatoire : il sera rempli a la volee dans ajouter_doleance si vide
	$erreurs = formulaires_editer_objet_verifier('article', $id_article, ['id_parent', 'titre', 'texte']);
	// si on utilise le formulaire dans le public
	if (!function_exists('autoriser')) {
		include_spip('inc/autoriser');
	}
	if (
		!isset($erreurs['id_parent'])
		and !autoriser('creerarticledans', 'rubrique', _request('id_parent'))
	) {
		$erreurs['id_parent'] = _T('info_creerdansrubrique_non_autorise') . ' n°' . $id_rubrique;
	}
	if (strlen(_request('texte')) < 50) {
		$erreurs['texte'] = 'Un peu trop court...' ;
	}
	return $erreurs;
}

/**
 * Traitements du formulaire d'édition d'article
 *
 * @uses formulaires_editer_objet_traiter()
 *
 * @param int|string $id_article
 *     Identifiant de l'article. 'new' pour une nouvel article.
 * @param int $id_rubrique
 *     Identifiant de la rubrique parente
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un article source de traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL de l'article, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Retours des traitements
 **/
function formulaires_ajouter_doleance_traiter_dist(
	$id_article = 'new',
	$id_rubrique = 0,
	$retour = '',
	$lier_trad = 0,
	$config_fonc = 'articles_edit_config',
	$row = [],
	$hidden = ''
) {
	// ici on ignore changer_lang qui est poste en cas de trad,
	// car l'heuristique du choix de la langue est pris en charge par article_inserer
	// en fonction de la config du site et de la rubrique choisie
	if (!$lier_trad) {
		set_request('changer_lang');
	}

	if ( isset($_COOKIE['time-doleance']) && (time() - $_COOKIE['time-doleance'] < 86400) ) {
		$r['message_erreur'] = 'Doléances déjà publiées il y a moins de 24 heures !';
		return $r;
	}

	include_spip('inc/cookie');
	spip_setcookie('time-doleance', time());

	$r = formulaires_editer_objet_traiter(
		'article',
		$id_article,
		$id_rubrique,
		$lier_trad,
		'',
		$config_fonc,
		$row,
		$hidden
	);
	$r['message_ok'] = "Vos doléances ont bien été enregistrées. Elle seront publiées prochainement.";
	return $r;
}
