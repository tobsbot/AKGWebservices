<?php
header('Content-Type: application/json; charset=utf-8');

$start = 0;
if(isset($_GET['start'])) {
	$start = $_GET['start'];
}

$count = 10;
if(isset($_GET['count'])) {
	$count = $_GET['count'];
}

$database = json_decode(file_get_contents("crons/lib/database.json"));
$conn = mysqli_connect(
	getenv($database ->server),
	$database ->credentials ->user,
	$database ->credentials ->passwd
);

if (!$conn) {
	json_response(NULL, 500, "Connection to database failed!");
}

if (!mysqli_select_db($conn, $database ->name)) {
	mysqli_close($conn);
	json_response(NULL, 500, "Selection of datasource failed!");
}

if(!mysqli_query($conn, "SET NAMES 'utf8'")) {
	printf("Setting character encoding failed: %s\r\n", mysqli_error($conn));
	mysqli_close($conn);
	json_response(NULL, 500, "Selection of character encoding failed: %s\r\n", mysqli_error($conn));
}

$sth = mysqli_query($conn, "SELECT * FROM News ORDER BY _id ASC LIMIT $count OFFSET $start");
$rows = array();
while($r = mysqli_fetch_assoc($sth)) {
    $rows[] = $r;
}

mysqli_close($conn);

$message = "OK.";
if(!isset($_GET['start'])) {
	$message .= " No 'start' parameter passed. Returning entries from first position.";
}

if(!isset($_GET['count'])) {
	$message .= " No 'count' parameter passed. Returning 10 entries.";
}

json_response($rows, 200, $message);

function json_response($data, $code = 200, $msg = "OK") {
	print json_encode([
		"code" => $code,
		"message" => $msg,
		"data" => $data
	], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	exit();
}
?>
