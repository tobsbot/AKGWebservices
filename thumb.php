<?php
require_once('phpthumb/phpThumb.config.php');
echo '<img src="'.htmlspecialchars(phpThumbURL('src=http://www.akg-bensheim.de/images/Mathematik/Kg2015_Preistrger_HP.JPG&w=320&q=50', 'phpthumb/phpThumb.php')).'">';
?>
