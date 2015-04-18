<?php
include('lib/utils.php');
include('lib/simple_html_dom.php');

header('Content-Type: text/plain; charset=utf-8');

define("URL_EVENTS", "http://www.akg-bensheim.de/termine/range.listevents/-");
define("SEL_EVENTS", "#jevents_body table.ev_table tbody tr[!valign]");


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

mysqli_query("TRUNCATE Events");

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

$html = str_get_html(
	get_data(URL_EVENTS),
	true, true, "ISO-8859-1"
);

if (empty($html)) {
	print("No resource on this url!\r\n");
	exit();
}

$arr = $html ->find(SEL_EVENTS);
if (count($arr) < 1) {
	print("No entries on this resource!\r\n");
	exit();
}

foreach($arr as $tr) {
	$rowHtml = str_get_html($tr ->innertext);

	$dateEl = $rowHtml ->find('td.ev_td_left', 0);
	$eventEl = $rowHtml ->find('li.ev_td_li');

	if( !isset($htmldf) ||
		!isset($dateEl) ||
		!isset($eventEl)||
		(count($eventEl) < 1)
	){
		continue;
	}

	foreach($eventEl as $event) {
		$title = tidyUp($event ->find('a.ev_link_row', 0)  ->plaintext);
		$dateString = tidyUp($li ->find('text', 0) ->plaintext);

		print("Element [$title, $dateString]");
	}

	/*
	$dateEl = $tr ->find('.ev_td_left text', 0);
	$eventEl = $tr ->find('.ev_td_li');

	echo count($dateEl) . " date has " . count($eventEl) . " events.\r\n";
	*/
}

/*
foreach($arr as $tr) {
	$el = tidyUp($tr ->find("td.ev_td_left text", 0) ->plaintext);

	if(!isset($el) || empty($el)) {
		continue;
	}

	$eventDate = eventD($el);
	//print($eventDate."\r\n");

	// Iterate through all events this date
	foreach($tr ->find('td.ev_td_right ul.ev_ul li.ev_td_li') as $li) {
		// Get the event title
		$title = tidyUp($li ->find('a.ev_link_row', 0)  ->plaintext);

		// Start the event description with a date string
		$description = tidyUp($li ->find('text', 0) ->plaintext) . ": ";

		// Load html of description page
		$tmpHtml = str_get_html(
			get_data("http://www.akg-bensheim.de" . ($li ->find('a.ev_link_row', 0) ->href)),
			true, true, "ISO-8859-1"
		);

		// Check description page
		if(!empty($tmpHtml)) {

			// Iterate through all layout rows that make up the description
			foreach($tmpHtml ->find('#jevents_body table.contentpaneopen tr[!class]') as $tr_temp) {

				// Add the description to the description string
				$description .= tidyUp($tr_temp ->plaintext);
	    	}
		}

		if(!event_exists($conn, $title, $eventDate)) {
			mysqli_stmt_execute($insert);
			printf(
				"%d row inserted: [$title, $eventDate, $description]\r\n",
				mysqli_stmt_affected_rows($insert)
			);
		} else {
			break 2;
		}
	}

}
*/

##########################################################
print("Closing connection to database...\r\n");
##########################################################

mysqli_stmt_close($insert);
mysqli_close($conn);

##########################################################
print("Cron job successfully finished!\r\n");
##########################################################
?>
