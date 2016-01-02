<?php
include('lib/utils.php');
include('lib/simple_html_dom.php');

header('Content-Type: text/plain; charset=utf-8');

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

$database = json_decode(file_get_contents("lib/database.json"));
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
	mysqli_close($conn);

	exit();
}

if(!mysqli_query($conn, "SET NAMES 'utf8'")) {
	printf("Setting character encoding failed: %s\r\n", mysqli_error($conn));
	mysqli_close($conn);

	exit();
}

if (!mysqli_query($conn, $database ->tables ->Substitution ->create) ||
	!mysqli_query($conn, $database ->tables ->Substitution ->clear)) {
	printf("Creation / Clearing failed: %s\r\n", mysqli_error($conn));

	mysqli_close($conn);
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

	foreach ($html ->find(SEL_SUBST) as $tr) {
		$formKey		= utf8_encode(tidyUp(	$tr->find('td', 0) ->plaintext));
		$date			= parseSubstDate(		$tr->find('td', 1) ->plaintext);
		$period			= utf8_encode(tidyUp(	$tr->find('td', 2) ->plaintext));
		$type			= utf8_encode(tidyUp(	$tr->find('td', 3) ->plaintext));
		$lesson			= utf8_encode(tidyUp(	$tr->find('td', 4) ->plaintext));
		$lessonSubst	= utf8_encode(tidyUp(	$tr->find('td', 5) ->plaintext));
		$room			= utf8_encode(tidyUp(	$tr->find('td', 6) ->plaintext));
		$roomSubst		= utf8_encode(tidyUp(	$tr->find('td', 7) ->plaintext));
		$annotation		= utf8_encode(tidyUp(	$tr->find('td', 8) ->plaintext));

		mysqli_stmt_execute($insert);
		printf(
			"%d row inserted: [$formKey, $date, $period, $type, $lesson, $lessonSubst, $room, $roomSubst, $annotation]\r\n",
			mysqli_stmt_affected_rows($insert)
		);
	}
}

##########################################################
print("\r\nClosing connection to database...\r\n");
##########################################################

mysqli_stmt_close($insert);
mysqli_close($conn);

##########################################################
print("Cron job successfully finished!\r\n");
##########################################################
?>
