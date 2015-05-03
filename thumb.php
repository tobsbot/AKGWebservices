<?php
require_once('phpthumb/phpThumb.config.php');

$file = phpThumbURL('http://www.akg-bensheim.de/images/Mathematik/Kg2015_Preistrger_HP.JPG&w=320&q=50', 'phpthumb/phpThumb.php');
$type = 'image/jpeg';
header('Content-Type:'.$type);
header('Content-Length: ' . filesize($file));
readfile($file);
?>
