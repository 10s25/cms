<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Traitement post du formulaire formulaires/f_cfg_acs_config.html
 * @return array
 */
function formulaires_f_cfg_10s25_config_traiter_dist() {
	refuser_traiter_formulaire_ajax();
	if (
		(lire_config('sq10s25/MIRROR_AUTH_IP') != _request('MIRROR_AUTH_IP'))
	) {
		ecrire_config('sq10s25/MIRROR_AUTH_IP', _request('MIRROR_AUTH_IP'));
		return ['message_ok' => 'Sauvegardé'];
	}
}
