<?php

function coupe($texte, $maxlen = 50) {
	if (strlen($texte) > $maxlen) {
		return substr($texte, 0, $maxlen) . '...';
	}
	return $texte;
}