<?php
require_once('fn.php');

use MapFile\Map;
use MapFile\Layer;

session_start();

$tmp = sys_get_temp_dir();
if (!file_exists($tmp.'/mapserver') || !is_dir($tmp.'/mapserver')) mkdir($tmp.'/mapserver');

$settings = parse_ini_file('settings.ini');
$mapscript = extension_loaded('mapscript');

$map = NULL;
if (isset($_SESSION['mapfile-generator']['source']) && file_exists($_SESSION['mapfile-generator']['source'])) $map = $_SESSION['mapfile-generator']['source'];
if (isset($_GET['map']) && file_exists(urldecode($_GET['map']))) $map = urldecode($_GET['map']);

if (is_null($map) || !isset($_GET['layer'])) { header('Location:index.php'); exit(); }

$meta = mapfile_getmeta($_SESSION['mapfile-generator']['mapfile']);
$layers = mapfile_getlayers($map);
$layer = $layers[intval($_GET['layer'])];

page_header();
?>
<h1>Map: <?= htmlentities($meta['name']) ?></h1>
<h2>Layer: <?= htmlentities($layer['name']) ?></h2>
<?php
var_dump($layer);
