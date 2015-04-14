<?php
include('lib/simple_html_dom.php');
header('Content-Type: text/plain; charset=utf-8');

define("CRED_FILE",	"lib/credentials.json");
define("URL_SUBST", "http://www.akg-bensheim.de/akgweb2011/content/Vertretung/w/%02d/w00000.htm");
define("SEL_SUBST", "#vertretung table.subst tr[class=list odd], #vertretung table.subst tr[class=list even]");

define("SQL_CREATE",
	"CREATE TABLE IF NOT EXISTS `Substitution` (
	`_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'KEY',
	`formKey` varchar(10) NOT NULL DEFAULT '',
	`date` date NOT NULL,
	`period` varchar(7) NOT NULL DEFAULT '1',
	`type` varchar(50) NOT NULL DEFAULT 'Sonstige',
	`lesson` varchar(10) NOT NULL ,
	`lessonSubst` varchar(10) NOT NULL DEFAULT '',
	`room` varchar(10) NOT NULL DEFAULT '',
	`roomSubst` varchar(10) NOT NULL DEFAULT '',
	`annotation` varchar(350) NOT NULL DEFAULT '',
	PRIMARY KEY (`_id`)) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8"
);
define("SQL_CLEAR", "TRUNCATE `Substitution`");
define("SQL_INSERT", "INSERT INTO `Substitution` (`formKey`, `date`, `period`, `type`, `lesson`, `lessonSubst`, `room`, `roomSubst`, `annotation`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

date_default_timezone_set('Europe/Berlin');
$weeks = array(
	date("W", strtotime("now")),
	date("W", strtotime('+1 Week'))
);

$credentials = json_decode(file_get_contents(CRED_FILE)) ->akgwebservices;
$conn = mysqli_connect(
	$credentials ->server,
	$credentials ->pair ->user,
	$credentials ->pair ->passwd
);

if (!$conn) {
	printf("Connect failed: %s\r\n", mysqli_connect_error());
	exit();
}


if(!mysqli_select_db($conn, "akgwebservices")) {
	printf("Selection failed: %s\r\n", mysqli_error($conn));
	exit();
}

/* return name of current default database */
if ($result = mysqli_query($conn, "SELECT DATABASE()")) {
    $row = mysqli_fetch_row($result);
    printf("Using database \"%s\":\r\n", $row[0]);
    mysqli_free_result($result);
}

if (!mysqli_query($conn, SQL_CREATE) || !mysqli_query($conn, SQL_CLEAR)) {
	printf("Creation / Clearing failed: %s\r\n", mysqli_error($conn));
	exit();
}

$insert = mysqli_prepare($conn, SQL_INSERT);
mysqli_stmt_bind_param($insert, 'sssssssss', $formKey, $date, $period, $type, $lesson, $lessonSubst, $room, $roomSubst, $annotation);

foreach($weeks as $week) {
	printf("Parsing \"" . URL_SUBST . "\" ...\r\n", $week);

	$html = str_get_html(get_data(sprintf(URL_SUBST, $week)));
	if(empty($html)) {
		print("No resource on this url!");
		continue;
	}

	$arr = $html ->find(SEL_SUBST);
	if(count($arr) < 1) {
		print("No entries on this resource!");
		continue;
	}

	foreach($arr as $tr) {
		$formKey		= tidyUp($tr->find('td', 0)	->plaintext);
		$date			= sqlDate($tr->find('td', 1) ->plaintext);
		$period			= tidyUp($tr->find('td', 2) ->plaintext);
		$type			= tidyUp($tr->find('td', 3) ->plaintext);
		$lesson			= tidyUp($tr->find('td', 4) ->plaintext);
		$lessonSubst	= tidyUp($tr->find('td', 5) ->plaintext);
		$room			= tidyUp($tr->find('td', 6) ->plaintext);
		$roomSubst		= tidyUp($tr->find('td', 7) ->plaintext);
		$annotation		= tidyUp($tr->find('td', 8) ->plaintext);

		mysqli_stmt_execute($insert);
		printf("%d row inserted: [$formKey,\t\t$date,\t$period,\t\t$type,\t$lesson,\t$lessonSubst,\t$room,\t$roomSubst,\t$annotation]\r\n", mysqli_stmt_affected_rows($insert));
	}

}

mysqli_stmt_close($insert);
mysqli_close($conn);

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
    $ret = html_entity_decode($str, ENT_COMPAT | ENT_HTML401, "ISO-8859-1");
	$ret = str_replace("\xA0", ' ', $ret);

	return trim($ret);
}

function sqlDate($str) {
	$tmp = tidyUp($str)
		. date("Y", strtotime("now"));

	return date('Y-m-d', strtotime($tmp));
}
?>
