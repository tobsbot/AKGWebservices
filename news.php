<?php
header('Content-Type: application/json; charset=utf-8');

$start = 0;
if(isset($_GET['start'])) {
	$start = $_GET['start'];
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

$sth = mysqli_query($conn, "SELECT * FROM News ORDER BY _id ASC LIMIT 10 OFFSET $start");
$rows = array();
while($r = mysqli_fetch_assoc($sth)) {
    $rows[] = $r;
}

mysqli_close($conn);
if(!isset($_GET['start']))
	json_response($rows, 200, "No start passed. Returned first 10 entries.");
else
	json_response($rows);

function json_response($data, $code = 200, $msg = "OK") {
	print json_encode([
		"code" => $code,
		"message" => $msg,
		"data" => $data
	], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	exit();
}
?>