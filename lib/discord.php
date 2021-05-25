<?php
// Functions related to Discord webhook stuff.

use \DiscordWebhooks\Client;
use \DiscordWebhooks\Embed;

/**
 * Trigger the new video webhook.
 *
 * @param array $video Video array with the necessary data.
 */
function newVideoHook($video) {
	global $webhook, $domain;

	// dirty description truncating
	if (strlen($video['description']) > 500) {
		$video['description'] = wordwrap($video['description'], 500);
		$video['description'] = substr($video['description'], 0, strpos($video['description'], "\n")) . '...';
	}

	$webhook = new Client($webhook);
	$mbd = new Embed();

	$mbd->title($video['name'])
		->description($video['description'])
		->url(sprintf("%s/watch.php?v=%s", $domain, $video['video_id']))
		->timestamp(date(DATE_ISO8601))
		->color(13056)
		->footer("squareBracket Videos")
		->thumbnail(sprintf("%s/assets/thumb/%s.png", $domain, $video['video_id']))
		->author(
			$video['u_name'],
			sprintf("%s/user.php?name=%s", $domain, $video['u_name'])
		);

	$webhook->embed($mbd)->send();
}
