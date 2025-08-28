<?php

define('RS_FROM_ICON', [
'bluesky'   => 'Bluesky',
'facebookp' => 'Facebook (pages)',
'facebookg' => 'Facebook (groupes)',
'instagram' => 'Instagram',
'piaille'   => 'Piaille',
'signal'    => 'Signal',
'site'      => 'Site web',
'telegram'  => 'Telegram',
'tiktok'    => 'TikTok',
'twitter'   => 'X (Twitter)',
]);

function rs_from_icon($icon) {
	return RS_FROM_ICON[$icon];
}