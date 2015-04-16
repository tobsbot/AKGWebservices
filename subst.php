<?php
header('Content-Type: application/json; charset=utf-8');

$database = json_decode(file_get_contents("crons/lib/database.json"));
$conn = mysqli_connect(
	getenv($database ->server),
	$database ->credentials ->user,
	$database ->credentials ->passwd
);

if (!$conn) {
	json_response(
		NULL, 500,
		sprintf("Connect failed: %s", mysqli_connect_error())
	);
}

if (!mysqli_select_db($conn, $database ->name)) {
	json_response(
		NULL, 500,
		sprintf("Selection failed: %s", mysqli_error($conn))
	);
}

$sth = mysqli_query($conn, "SELECT * FROM Substitution");
$rows = array();
while($r = mysqli_fetch_assoc($sth)) {
    $rows[] = $r;
}

mysqli_close($conn);
json_response($rows);

function json_response($data, $code = 200, $msg = "OK") {
	print json_encode([
		"code" => $code,
		"message" => $msg,
		"data" => $data
	], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	exit();
}
?>
