<?php
header('Content-Type: text/plain; charset=utf-8');

if(isset($_GET['type'])) {
  $type = $_GET['type'];
}

$path = getenv('OPENSHIFT_DATA_DIR') . '/logs';
switch ($type) {
  case 'subst':
    $path .= '/subst';
    break;

  case 'event':
    $path .= '/event';
    break;

  case 'news':
    $path .= '/news';
    break;

  default:
    die('No log information available.');
    break;
  }

  die(file_get_contents($path));
?>
