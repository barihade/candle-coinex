<?php
	error_reporting(0);
	$imagedata = $_POST['imgdata'];
	$data = str_replace("data:image/png;base64,", "", $imagedata);
	// save to file
	file_put_contents("image.png", base64_decode($data));
?>