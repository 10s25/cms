<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Traitement post du formulaire formulaires/f_cfg_acs_config_miroir.html
 * @return array
 */
function formulaires_f_cfg_10s25_config_miroir_traiter_dist() {
	refuser_traiter_formulaire_ajax();
	$src = _request('MIRROR_SOURCE');
	if ($src && !filter_var($src, FILTER_VALIDATE_URL)) {
		return ['message_erreur' => "\"$src\" n'est pas une URL valide."];
	}
	if (
		(lire_config('sq10s25/MIRROR_SOURCE') != $src)
	) {
		ecrire_config('sq10s25/MIRROR_SOURCE', $src);
		return ['message_ok' => 'Sauvegardé'];
	}
}
