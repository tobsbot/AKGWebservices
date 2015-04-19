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

	return trim($ret);
}

function parseSubstDate($str) {
	$tmp = tidyUp($str)
		. date("Y", strtotime("now"));

	return date('Y-m-d', strtotime($tmp));
}

function parseEventDate($str) {
	$months = array(
		"Januar"	=> "January",
		"Februar"	=> "February",
		"März"		=> "March",
		"April"		=> "April",
		"Mai"		=> "May",
		"Juni"		=> "June",
		"August"	=> "August",
		"September" => "September",
		"Oktober" 	=> "October",
		"November"	=> "November",
		"Dezember"	=> "December"
	);

	return strftime("%Y-%m-%d", strtotime(strtr($str, $months)));
}
?>
