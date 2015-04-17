<?php
function get_data($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_ENCODING, "ISO-8859-1");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

function tidyUp($str) {
	$ret = strip_tags($str);
    $ret = html_entity_decode($str, ENT_COMPAT | ENT_HTML401, "ISO8859-1");
	$ret = str_replace("\xA0", '', $ret);

	return utf8_encode(trim($ret));
}

function sqlDate($str) {
	$tmp = tidyUp($str)
		. date("Y", strtotime("now"));

	return date('Y-m-d', strtotime($tmp));
}
?>
