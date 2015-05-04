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
	$ret = str_replace('/\s+/S', " ", $string);

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

function getImg($str) {
	if(startsWith($str, "/images/")) {
		return "http://www.akg-bensheim.de$str";
	} else if (startsWith($str, "http://")) {
		return $str;
	} else {
		return "";
	}
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}
?>
