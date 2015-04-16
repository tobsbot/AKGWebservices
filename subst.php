<?php
header('Content-Type: application/json; charset=utf-8');

$database = json_decode(file_get_contents("crons/lib/database.json"));
$conn = mysqli_connect(
	getenv($database ->server),
	$database ->credentials ->user,
	$database ->credentials ->passwd
);

if (!$conn) {
	printf("Connect failed: %s\r\n", mysqli_connect_error());
	exit();
}

if (!mysqli_select_db($conn, $database ->name)) {
	printf("Selection failed: %s\r\n", mysqli_error($conn));
	exit();
}

$sth = mysqli_query($conn, "SELECT * FROM Substitution");
$rows = array();
while($r = mysqli_fetch_assoc($sth)) {
    $rows[] = $r;
}
json_response($rows);
mysqli_close($conn);

function json_response($code, $msg, $data) {
	print json_encode([
		"code" => $code,
		"msg" => $msg,
		"data" => $data
	], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	exit();
}
?>
