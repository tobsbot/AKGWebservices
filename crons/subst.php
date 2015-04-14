<?php
include('lib/log.php');
include('lib/simple_html_dom.php');

define("URL_SUBST", "http://www.akg-bensheim.de/akgweb2011/content/Vertretung/w/%02d/w00000.htm");
define("SEL_SUBST", "#vertretung table.subst tr[class=list odd], #vertretung table.subst tr[class=list even]");

$credentials = json_decode(file_get_contents('lib/credentials.json')) ->akgwebservices ->pair;
$log = new Log('log/subst.txt');
?>
