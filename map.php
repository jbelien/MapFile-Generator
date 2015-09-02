<?php
require_once('fn.php');

use MapFile\Map;
use MapFile\Layer;

session_start();

$tmp = sys_get_temp_dir();
if (!file_exists($tmp.'/mapserver') || !is_dir($tmp.'/mapserver')) mkdir($tmp.'/mapserver');

$settings = parse_ini_file('settings.ini');
$mapscript = extension_loaded('mapscript');

$source = NULL; $mapfile = NULL;
if (isset($_SESSION['mapfile-generator']['source']) && file_exists($_SESSION['mapfile-generator']['source'])) $source = $_SESSION['mapfile-generator']['source'];
if (isset($_SESSION['mapfile-generator']['mapfile']) && file_exists($_SESSION['mapfile-generator']['mapfile'])) $mapfile = $_SESSION['mapfile-generator']['mapfile'];

if (/*is_null($source) || */is_null($mapfile)) { header('Location:index.php'); exit(); }

page_header();
?>
<div class="container">
  <div class="row">
    <div class="col-sm-7">
      <div class="panel panel-default">
        <div class="panel-heading">
          Map:
          <a target="_blank" class="pull-right" style="text-decoration:none;" href="<?= $settings['mapserv'] ?>?map=<?= $mapfile ?>&mode=browse&layers=all&template=openlayers">MapServer Viewer <i class="fa fa-external-link"></i></a>
        </div>
        <div class="panel-body"><img class="img-responsive center-block"  alt="Unable to draw the map." src="<?= $settings['mapserv'] ?>?map=<?= $mapfile ?>&amp;mode=map&amp;layers=all&amp;<?= time() ?>"></div>
      </div>
    </div>
    <div class="col-sm-5">
      <div class="panel panel-default">
        <div class="panel-heading">Scalebar:</div>
        <div class="panel-body"><img class="img-responsive center-block" alt="Unable to draw the scalebar." id="map-scalebar" src="<?= $settings['mapserv'] ?>?map=<?= $mapfile ?>&amp;mode=scalebar&amp;layers=all&amp;<?= time() ?>"></div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">Legend:</div>
        <div class="panel-body"><img class="img-responsive center-block" alt="Unable to draw the legend." id="map-legend" src="<?= $settings['mapserv'] ?>?map=<?= $mapfile ?>&amp;mode=legend&amp;layers=all&amp;<?= time() ?>"></div>
      </div>
    </div>
  </div>
</div>
<?php
page_footer();