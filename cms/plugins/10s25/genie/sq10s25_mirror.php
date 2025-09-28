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
		spip_log('genie_sq10s25_mirror: unable to fetch "' . $src . '" in ' . ZIP_FILENAME, _LOG_ALERTE_ROUGE);
		return 0;
	}
	$zip = new ZipArchive;
	if ($zip->open(ZIP_FILENAME) === TRUE) {
		$zip->extractTo($dir_miroir);
		$zip->close();
		unlink(ZIP_FILENAME);
		unlink($dir_miroir . '/.htaccess');
		rec_move($dir_miroir . '/IMG', $cms_root . '/IMG');
		rmdir($dir_miroir . '/IMG');
		rec_move($dir_miroir . '/tmp/dump', $cms_root . '/tmp/dump');
		rmdir($dir_miroir . '/tmp/dump');
		rmdir($dir_miroir . '/tmp');
		rmdir($dir_miroir);
		// TODO  : restaurer la sauvegarde sqlite

	} else {
		return 0;
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
