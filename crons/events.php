<?php
include('lib/utils.php');
include('lib/simple_html_dom.php');

header('Content-Type: text/plain; charset=utf-8');

define("URL_EVENTS", "http://www.akg-bensheim.de/termine/range.listevents/-");
define("SEL_EVENTS", "#jevents_body .ev_table tr td.ev_td_right ul.ev_ul li.ev_td_li");


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
	exit();
}

if (!mysqli_query($conn, $database ->tables ->Events ->create) {
	printf("Creation / Clearing failed: %s\r\n", mysqli_error($conn));
	exit();
}

$insert = mysqli_prepare(
	$conn,
	$database ->tables ->Events ->insert
);

mysqli_stmt_bind_param(
	$insert,
	'sss',
	$title,
	$eventDate,
	$description
);

print("\r\nParsing \"" . URL_EVENTS . "\" ...\r\n");
?>
