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

if (is_null($source) || is_null($mapfile) || !isset($_GET['layer'])) { header('Location:index.php'); exit(); }

$meta = mapfile_getmeta($mapfile);
$layers = mapfile_getlayers($mapfile);
$layer = $layers[intval($_GET['layer'])];

if (isset($_POST['action']) && $_POST['action'] == 'save') {
  if ($mapscript) {
    $map = new mapObj($mapfile);

    if (isset($_GET['layer']))
      try { $layer = $map->getLayer(intval($_GET['layer'])); } catch (MapScriptException $e) { $layer = new layerObj($map); }
    else
      $layer = new layerObj($map);

    $layer->minscaledenom = floatval($_POST['minscaledenom']);
    $layer->maxscaledenom = floatval($_POST['maxscaledenom']);
    $layer->opacity = intval($_POST['opacity']);
    $layer->labelitem = $_POST['labelitem'];
    $layer->classitem = $_POST['classitem'];

    $map->save($mapfile);
    $map->free(); unset($map);
  } else {
  }

  header('Location: index.php');
  exit();
}

page_header('Layer: '.$layer['name']);
?>
<div class="container">
  <h1>Map: <a href="index.php"><?= htmlentities($meta['name']) ?></a></h1>
  <h2>Layer: <?= htmlentities($layer['name']) ?></h2>

  <form class="form-horizontal" action="layer-class.php" method="post">
    <div class="form-group">
      <label for="inputMinScaleDenom" class="col-sm-2 control-label">Min. Scale Denom.</label>
      <div class="col-sm-10">
        <input type="number" class="form-control" id="inputMinScaleDenom" name="minscaledenom" value="<?= $layer['minscaledenom'] ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="inputMaxScaleDenom" class="col-sm-2 control-label">Max. Scale Denom.</label>
      <div class="col-sm-10">
        <input type="number" class="form-control" id="inputMaxScaleDenom" name="maxscaledenom" value="<?= $layer['maxscaledenom'] ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="inputOpacity" class="col-sm-2 control-label">Opacity</label>
      <div class="col-sm-10">
        <div class="input-group">
          <input type="range" class="form-control" id="inputOpacity" name="opacity" value="<?= intval($layer['opacity']) ?>" min="0" max="100" step="5">
          <span class="input-group-addon"><?= intval($layer['opacity']) ?></span>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for="inputClassItem" class="col-sm-2 control-label">Class Item</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="inputClassItem" name="classitem" value="<?= $layer['classitem'] ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="inputLabelItem" class="col-sm-2 control-label">Label Item</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="inputLabelItem" name="labelitem" value="<?= $layer['labelitem'] ?>">
      </div>
    </div>
    <div class="form-group text-center">
      <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o"></i> Save</button>
      <a href="index.php" class="btn btn-default"><i class="fa fa-backward"></i> Cancel</a>
    </div>
  </form>

  <hr>

  <div class="row">
    <div class="col-sm-8">
      <table class="table table-striped">
        <thead>
          <tr>
            <th></th>
            <th>Class</th>
            <th>Expression</th>
            <th colspan="2"></th>
            <th style="border-left: 1px solid #DDD;">Styles</th>
            <th>Labels</th>
          </tr>
        </thead>
        <tbody>
<?php
        foreach ($layer['class'] as $i => $c) {
          echo '<tr>';
            echo '<td class="text-right">'.($i+1).'.</td>';
            echo '<td>'.htmlentities($c['name']).'</td>';
            echo '<td>'.htmlentities($c['expression']).'</td>';
            echo '<td style="width:20px; text-align:center;"><a href="#"" title="Edit"><i class="fa fa-pencil-square-o"></i></a></td>';
            echo '<td style="width:20px; text-align:center;"><a href="#" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
            echo '<td style="border-left: 1px solid #DDD;"><a href="layer-style-label.php?layer='.intval($_GET['layer']).'&amp;class='.$i.'&amp;style" style="text-decoration:none;"><i class="fa fa-paint-brush"></i> '.count($c['style']).' style'.(count($c['style']) > 1 ? 's' : '').'</a></td>';
            echo '<td><a href="layer-style-label.php?layer='.intval($_GET['layer']).'&amp;class='.$i.'&amp;label" style="text-decoration:none;"><i class="fa fa-font"></i> '.count($c['label']).' label'.(count($c['label']) > 1 ? 's' : '').'</a></td>';
          echo '</tr>'.PHP_EOL;
        }
?>
        </tbody>
      </table>
    </div>
    <div class="col-sm-4">
      <img style="margin:auto;" src="<?= $settings['mapserv'] ?>?map=<?= $mapfile ?>&amp;mode=legend&amp;layers=<?= $layer['name'] ?>&amp;<?= time() ?>" alt="Legend &quot;<?= htmlentities($layer['name']) ?>&quot;" class="thumbnail">
    </div>
  </div>

</div>

<script>
  $(document).ready(function() {
    $('input[type=range]').on('change input', function() { $(this).parent('.input-group').find('.input-group-addon').text($(this).val()); });
  });
</script>
<?php
page_footer();