<?php
session_start();

$mapscript = extension_loaded('mapscript');

if ($mapscript && isset($_SESSION['mapfile-generator']['mapfile'], $_GET['layer'])) {
  $_map = new mapObj($_SESSION['mapfile-generator']['mapfile']);

  header('Content-Type: text/xml');

  $l = $_map->getLayer(intval($_GET['layer']));
  $l->set('status', MS_ON);

  echo $_map->generateSLD();
}

exit();