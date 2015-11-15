<?php
header('Content-Type: text/plain; charset=utf-8');

if(isset($_GET['file'])) {
  $file = $_GET['file'];
}

$path = getenv('OPENSHIFT_DATA_DIR') . '/logs';
switch ($file) {
  case 'subst':
    $file .= '/subst';
    break;

  case 'event':
    $file .= '/event';
    break;

  case 'news':
    $file .= '/news';
    break;

  default:
    die('No log information available.');
    break;
  }

  if(isset($_GET['time'])) {
    echo filemtime($path);
  }

  if(isset($_GET['show'])) {
    echo file_get_contents($path);
  }

  exit;
?>
