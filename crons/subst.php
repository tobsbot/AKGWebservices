<?php
include('lib/log.php');
include('lib/simple_html_dom.php');

define("LOG_FILE",	"log/subst.txt");
define("CRED_FILE",	"lib/credentials.json");
define("URL_SUBST", "http://www.akg-bensheim.de/akgweb2011/content/Vertretung/w/%02d/w00000.htm");
define("SEL_SUBST", "#vertretung table.subst tr[class=list odd], #vertretung table.subst tr[class=list even]");

$credentials = json_decode(file_get_contents(CRED_FILE))
	->akgwebservices ->pair;
$log = new Log(LOG_FILE);
?>
