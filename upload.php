<?php
//this uploads and converts the video, should switch to a better solution!
require('lib/common.php');

//TODO: make video IDs not use multiple underscores.

$video_id = substr(base64_encode(md5(bin2hex(random_bytes(6)))), 0, 11); //you are never too sure how much randomness you need.
$new = '';
foreach(str_split($video_id) as $char){
	switch (rand(0, 4)) {
		case rand(0, 1):
			$char = str_rot13($char);
			break;
		case rand(0, 2):
			$char = '_';
			break;
		case rand(0, 3):
			$char = mb_strtoupper($char);
			break;
		case rand(0, 4):
			$char = '-';
			break;
	}
	$new .= $char;
}

if (isset($_POST['upload']) and isset($currentUser['username'])) {
	$title = (isset($_POST['title']) ? $_POST['title'] : null);
	$description = (isset($_POST['desc']) ? $_POST['desc'] : null);

	// Prevent videos with duplicate metadata since they are probably accidentally uploaded.
	if (result("SELECT COUNT(*) FROM videos WHERE title = ? AND description = ?", [$title, $description])) {
		die("Your video is already uploading or has been uploaded.");
	}

	// Rate limit uploading to 2 minutes, both to prevent spam and to prevent double uploads.
	if (result("SELECT COUNT(*) FROM videos WHERE time > ? AND author = ?", [time() - 60*2, $currentUser['id']])) {
		die("Please wait 2 minutes before uploading again. If you've already uploaded a video, it is being processed.");
	}

	$name       = $_FILES['fileToUpload']['name'];
	$temp_name  = $_FILES['fileToUpload']['tmp_name'];  // gets video info and thumbnail info
	$ext  = pathinfo( $_FILES['fileToUpload']['name'], PATHINFO_EXTENSION );
	$target_file = 'videos/'.$new.'.'.$ext;
	if (move_uploaded_file($temp_name, $target_file)){
		query("INSERT INTO videos (video_id, title, description, author, time, tags, videofile, flags) VALUES (?,?,?,?,?,?,?,?)",
			[$new,$title,$description,$currentUser['id'],time(),json_encode(explode(', ', $_POST['tags'])),'videos/'.$new.'.mpd', 0x2]);

		// TODO: Windows Support
		system(sprintf('php lib/scripts/processingworker.php "%s" "%s" > /dev/null 2>&1 &', $new, $target_file));

		redirect('./watch.php?v='.$new);
	}
}

$twig = twigloader();
echo $twig->render('upload.twig');
