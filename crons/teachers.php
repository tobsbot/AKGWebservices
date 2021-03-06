<?php
include('lib/utils.php');
include('lib/simple_html_dom.php');

header('Content-Type: text/plain; charset=utf-8');

##########################################################
print("Establishing connection to database...\r\n");
#########################################################

$database = json_decode(file_get_contents("lib/database.json"));
$conn = mysqli_connect(
	getenv($database ->server),
	getenv($database ->credentials ->user),
	getenv($database ->credentials ->passwd)
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

if (!mysqli_query($conn, $database ->tables ->Teachers ->create) ||
	!mysqli_query($conn, $database ->tables ->Teachers ->clear)) {
	printf("Creation / Clearing failed: %s\r\n", mysqli_error($conn));

	mysqli_close($conn);
	exit();
}

$insert = mysqli_prepare(
	$conn,
	$database ->tables ->Teachers ->insert
);

mysqli_stmt_bind_param(
	$insert,
	'sssss',
	$firstName,
	$lastName,
	$shorthand,
	$subjects,
	$email
);

$json = json_decode(get_data('http://www.akgbensheim.de/support/teachers.json')) ->teachers;
foreach($json as $teacher) {
	$firstName = $teacher ->firstname;
	$lastName = $teacher ->lastname;
	$shorthand = $teacher ->shortname;
	$subjects = $teacher ->subjects;
	$email = $teacher ->email;

	mysqli_stmt_execute($insert);
	printf(
		"%d row inserted: [$firstName, $lastName, $shorthand, $subjects, $email]\r\n",
		mysqli_stmt_affected_rows($insert)
	);
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
