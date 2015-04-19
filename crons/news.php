<?php
include('lib/utils.php');
include('lib/simple_html_dom.php');

header('Content-Type: text/plain; charset=utf-8');
define("URL", "http://www.akg-bensheim.de/?start=%d");


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

if (!mysqli_query($conn, $database ->tables ->News ->create) ||
	!mysqli_query($conn, $database ->tables ->News ->clear)) {
	printf("Creation / Clearing failed: %s\r\n", mysqli_error($conn));
	exit();
}

$insert = mysqli_prepare(
	$conn,
	$database ->tables ->News ->insert
);

mysqli_stmt_bind_param(
	$insert,
	'ssss',
	$title,
	$article,
	$imageUrl,
	$imageDesc
);

$start = 0;
$end = 0;
do {
	printf("\r\nParsing \"" . URL . "\" ...\r\n", $start);

	$html = str_get_html(
		get_data(
			sprintf(URL, $start)
		)
	);

	if(empty($html)) {
		print("No resource on this url!\r\n");
		continue;
	}

	preg_match('/^\/\?\w+=(\d+)$/',
		$html ->find('li.pagination-end a.pagenav', 0) ->href, $matches);

	if( isset($matches[1]) &&
		!empty($matches[1]) ) {
		$end = $matches[1];
	}

	foreach($html ->find('#content_startseite div.blog-featured div.items-row') as $news) {
		$titleEl = $news ->find('h2.item-title a', 0);
		$title = tidyUp($titleEl ->plaintext);

		$tmpHtml = str_get_html(get_data('http://www.akg-bensheim.de' . $titleEl ->href));

		$article = "";
		if(!empty($tmpHtml)) {
			$imageUrl = 'http://www.akg-bensheim.de' . $tmpHtml ->find('div.item-page img', 0) ->src;
			$imageDesc = tidyUp($tmpHtml ->find('.img_caption', 0) ->outertext);

			foreach($tmpHtml ->find('div.item-page p[!class], div.item-page div[!class]') as $p_tmp) {
				$article .= tidyUp($p_tmp ->plaintext);
	    	}
		}

		mysqli_stmt_execute($insert);
		printf(
			"Parsed: [$title, $article, $imageUrl, $imageDesc] -> inserted into %d row of the database.\r\n ",
			mysqli_stmt_affected_rows($insert)
		);
	}

	$start += 4;
} while ($start <= $end);

##########################################################
print("\r\nClosing connection to database...\r\n");
##########################################################

mysqli_stmt_close($insert);
mysqli_close($conn);

##########################################################
print("Cron job successfully finished!\r\n");
##########################################################
?>
