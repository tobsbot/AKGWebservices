<?php
include('lib/simple_html_dom.php');
header('Content-Type: text/plain; charset=utf-8');

define("CRED_FILE",	"lib/database.json");
define("URL_SUBST", "http://www.akg-bensheim.de/akgweb2011/content/Vertretung/w/%02d/w00000.htm");
define("SEL_SUBST", "#vertretung table.subst tr[class=list odd], #vertretung table.subst tr[class=list even]");

date_default_timezone_set('Europe/Berlin');
$weeks = array(
	date("W", strtotime("now")),
	date("W", strtotime('+1 Week'))
);

##########################################################
print("Establishing connection to database...\r\n");
#########################################################

$database = json_decode(file_get_contents(CRED_FILE));
$conn = mysqli_connect(
	getenv($database ->server),
	$database ->credentials ->user,
	$database ->credentials ->passwd
);

if (!$conn) {
	printf("Connect failed: %s\r\n", mysqli_connect_error());
	exit();
}

##########################################################
print("Connected to database. Ensuring datasource...\r\n");
##########################################################

if (!mysqli_select_db($conn, $database ->name)) {
	printf("Selection failed: %s\r\n", mysqli_error($conn));
	exit();
}

if (!mysqli_query($conn, $database ->tables ->Substitution ->create) ||
	!mysqli_query($conn, $database ->tables ->Substitution ->prepare)) {
	printf("Creation / Clearing failed: %s\r\n", mysqli_error($conn));
	exit();
}

$insert = mysqli_prepare(
	$conn,
	$database ->tables ->Substitution ->insert
);

mysqli_stmt_bind_param(
	$insert,
	'sssssssss',
	$formKey,
	$date,
	$period,
	$type,
	$lesson,
	$lessonSubst,
	$room,
	$roomSubst,
	$annotation
);

foreach($weeks as $week) {
	printf("\r\nParsing \"" . URL_SUBST . "\" ...\r\n", $week);

	$html = str_get_html(
		get_data(sprintf(URL_SUBST, $week)),
		true, true, "ISO-8859-1"
	);

	if (empty($html)) {
		print("No resource on this url!\r\n");
		continue;
	}

	$arr = $html ->find(SEL_SUBST);
	if (count($arr) < 1) {
		print("No entries on this resource!\r\n");
		continue;
	}

	foreach ($arr as $tr) {
		$formKey		= tidyUp($tr->find('td', 0)	->plaintext);
		$date			= sqlDate($tr->find('td', 1)->plaintext);
		$period			= tidyUp($tr->find('td', 2) ->plaintext);
		$type			= tidyUp($tr->find('td', 3) ->plaintext);
		$lesson			= tidyUp($tr->find('td', 4) ->plaintext);
		$lessonSubst	= tidyUp($tr->find('td', 5) ->plaintext);
		$room			= tidyUp($tr->find('td', 6) ->plaintext);
		$roomSubst		= tidyUp($tr->find('td', 7) ->plaintext);
		$annotation		= tidyUp($tr->find('td', 8) ->plaintext);

		mysqli_stmt_execute($insert);
		printf(
			"%d row inserted: [$formKey, $date, $period, $type, $lesson, $lessonSubst, $room, $roomSubst, $annotation]\r\n",
			mysqli_stmt_affected_rows($insert)
		);
	}
}

##########################################################
print("Closing connection to database...\r\n");
##########################################################

mysqli_stmt_close($insert);
mysqli_close($conn);

##########################################################
print("Cron job successfully finished!\r\n");
##########################################################

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
    $ret = html_entity_decode($str, ENT_COMPAT | ENT_HTML401, "UTF-8");
	$ret = str_replace("\xA0", ' ', $ret);

	return utf8_encode(trim($ret));
}

function sqlDate($str) {
	$tmp = tidyUp($str)
		. date("Y", strtotime("now"));

	return date('Y-m-d', strtotime($tmp));
}
?>
