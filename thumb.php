<?php
require_once('phpthumb/phpThumb.config.php');
echo '<img src="'.htmlspecialchars(phpThumbURL('src=http://www.akg-bensheim.de/images/Mathematik/Kg2015_Preistrger_HP.JPG&w=300&q=30', 'phpthumb/phpThumb.php')).'">';
?>
