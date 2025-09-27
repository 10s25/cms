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
	if (lire_config('sq10s25/MIRROR_AUTH_IP') != _request('MIRROR_AUTH_IP')
	) {
		$ips = [];
		$liste_ips = explode(' ', _request('MIRROR_AUTH_IP'));
		foreach($liste_ips as $ip) {
			$ip = trim($ip);
			if (!$ip) break;
			if (filter_var($ip, FILTER_VALIDATE_IP)) {
				$ips[] = $ip;
			} else {
				return ['message_erreur' => "\"$ip\" n'est pas une adresse IP valide."];
			}
		}
		$htaccess = '';
		foreach($ips as $ip) {
			$htaccess .= 'Require ip ' . $ip . "\n";
		}
		if (strlen($htaccess)) {
			ecrire_fichier(_DIR_TMP . 'mes_fichiers/.htaccess', $htaccess);
		}
		ecrire_config('sq10s25/MIRROR_AUTH_IP', implode(' ', $ips));
		return ['message_ok' => 'Sauvegardé'];
	}
}
