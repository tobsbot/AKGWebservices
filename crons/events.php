<?php
include('lib/utils.php');
include('lib/simple_html_dom.php');

header('Content-Type: text/plain; charset=utf-8');
define("URL", "http://www.akg-bensheim.de/termine/range.listevents/-");


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

if (!mysqli_query($conn, $database ->tables ->Events ->create)) {
	printf("Creation / Clearing failed: %s\r\n", mysqli_error($conn));
	exit();
}

$insert = mysqli_prepare(
	$conn,
	$database ->tables ->Events ->insert
);

mysqli_stmt_bind_param(
	$insert,
	'ssss',
	$title,
	$eventDate,
	$dateString,
	$description
);

print("\r\nParsing \"" . URL . "\" ...\r\n");

$html = str_get_html(
	get_data(URL)
);

if (empty($html)) {
	print("No resource on this url!\r\n");
	exit();
}

foreach($html ->find('#jevents_body table.ev_table tbody tr') as $tr) {
	$tmp = trim($tr ->plaintext);
	if(empty($tmp)) {
		continue;
	}

	$eventDate = parseEventDate($tr ->find('td.ev_td_left text', 0) ->plaintext);
	foreach($tr ->find('li.ev_td_li') as $li) {
		$a = $li ->find('a.ev_link_row', 0);

		$title = tidyUp($a ->plaintext);
		$dateString = tidyUp($li ->find('text', 0) ->plaintext);

		$htmlDetail = str_get_html(get_data("http://www.akg-bensheim.de" . $a ->href));
		$description = "";
		if(!empty($htmlDetail)) {
			foreach($htmlDetail ->find('#jevents_body table.contentpaneopen tr[!class]') as $tr_temp) {
				$description .= tidyUp($tr_temp ->plaintext);
	    	}
		}

		print(event_exists($conn, $title, $eventDate));

		if(event_exists($conn, $title, $eventDate) == false) {
			mysqli_stmt_execute($insert);
			printf(
				"%d row inserted: [$title, $eventDate, $dateString, $description]\r\n",
				mysqli_stmt_affected_rows($insert)
			);
		} else {
			break 2;
		}
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
?>
