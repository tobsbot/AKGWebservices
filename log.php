<?php
header('Content-Type: text/plain; charset=utf-8');

if(isset($_GET['file'])) {
  $file = $_GET['file'];
}

$path = getenv('OPENSHIFT_DATA_DIR') . '/logs';
switch ($file) {
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

  $lastRun = filemtime($path);
  if(isset($_GET['time'])) {
    die("".$lastRun);
  }

  echo strftime("Last script run on %A, %d.%m.%Y at %T\n", $lastRun);
  echo file_get_contents($path);

  exit;
?>
