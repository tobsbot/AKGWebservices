<?php
header('Content-Type: application/json; charset=utf-8');
define("CRED_FILE",	"crons/lib/database.json");

$database = json_decode(file_get_contents(CRED_FILE));
$conn = mysqli_connect(
	getenv($database ->server),
	$database ->credentials ->user,
	$database ->credentials ->passwd
);

$sth = mysqli_query($conn, "SELECT * FROM Substitution");
$rows = array();
while($r = mysqli_fetch_assoc($sth)) {
    $rows[] = $r;
}
print json_encode($rows);
?>
