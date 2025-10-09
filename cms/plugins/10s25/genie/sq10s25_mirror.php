<?php

function genie_sq10s25_mirror_dist($t) {
	$src = lire_config('sq10s25/MIRROR_SOURCE');
	if (!$src) {
		return 0;
	}
	$src .= '/cms/?page=master';
	$cms_root = $_SERVER['DOCUMENT_ROOT'] . '/cms';
	$dir_miroir =	$cms_root . '/tmp/miroir';
	if (!is_dir($dir_miroir)) {
		mkdir($dir_miroir);
	}
	define('ZIP_FILENAME', $dir_miroir . '/master.zip');
	try {
		copy($src, ZIP_FILENAME);
	}
	catch(Exception $e) {
		spip_log('genie_sq10s25_mirror: unable to fetch "' . $src . '" in ' . ZIP_FILENAME, _LOG_CRITIQUE);
		return 0;
	}
	$zip = new ZipArchive;
	$zipret = $zip->open(ZIP_FILENAME);
	if (!$zipret) {
		spip_log('genie_sq10s25_mirror: unable to open "' . ZIP_FILENAME . '". Erreur ' . $zipret, _LOG_CRITIQUE);
		return 0;
	}
	$zip->extractTo($dir_miroir);
	$zip->close();
	unlink(ZIP_FILENAME);
	unlink($dir_miroir . '/.htaccess');
	rec_move($dir_miroir . '/IMG', $cms_root . '/IMG');
	rmdir($dir_miroir . '/IMG');
	$dump_dir = $dir_miroir . '/tmp/dump';
	if (!is_dir($dump_dir)) {
		spip_log('genie_sq10s25_mirror: "' . $dump_dir . '" n\'est pas un dossier accessible.', _LOG_CRITIQUE);
		return 0;
	}
	$dirhandle = opendir($dump_dir);
	while (false !== ($entry = readdir($dirhandle))) {
		if ($entry != "." && $entry != "..") {
			$filename = $entry;
			break;
		}
	}
	closedir($dirhandle);
	rec_move($dir_miroir . '/tmp/dump', $cms_root . '/tmp/dump');
	rmdir($dir_miroir . '/tmp/dump');
	rmdir($dir_miroir . '/tmp');
	rmdir($dir_miroir);
	$sauvegarde = $cms_root . '/tmp/dump/' . $filename;
	return restaurer_sauvegarde_sqlite($sauvegarde);
}


function restaurer_sauvegarde_sqlite($sauvegarde) {
	// Sauvegarder url ressources
	$url_statique_ressources = lire_config('url_statique_ressources');
	spip_log('restaurer_sauvegarde_sqlite: restauration de "' . $sauvegarde . '".', _LOG_INFO_IMPORTANTE);
	// todo

	// Restaurer url ressources
	if ($url_statique_ressources) {
		ecrire_config('url_statique_ressources', $url_statique_ressources);
	}
	return 1;
}



/**
 * Move récursif
 *
 * @param string $src
 * @param string $dst
 */
function rec_move($src, $dst) {
	// open the source directory
	$dir = opendir($src);

	// Make the destination directory if not exist
	@mkdir($dst);

	// Loop through the files in source directory
	while( $file = readdir($dir) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				// Recursively calling custom copy function
				// for sub directory
				rec_move($src . '/' . $file, $dst . '/' . $file);
				rmdir($src . '/' . $file);
			}
			else {
				rename($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}
