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

$meta = mapfile_getmeta($mapfile);
$layers = mapfile_getlayers($mapfile);

if (isset($_GET['layer'])) $layer = $layers[intval($_GET['layer'])];

if ($mapscript && isset($_POST['action']) && $_POST['action'] == 'save') {
  $map = new mapObj($mapfile);

  if (isset($_GET['layer']))
    try { $layer = $map->getLayer(intval($_GET['layer'])); } catch (MapScriptException $e) { $error = $e->getMessage(); }
  else
    $layer = new layerObj($map);

  $layer->tileitem = NULL;

  $layer->type = intval($_POST['type']);
  $layer->name = trim($_POST['name']);
  $layer->setProjection($_POST['projection']);
  $layer->setConnectionType($_POST['connectiontype']);
  $layer->connection = $_POST['connection'];
  $layer->data = $_POST['data'];
  $layer->setFilter($_POST['filter']);
  $layer->group = $_POST['group'];

  $layer->free(); unset($layer);

  $map->save($mapfile);
  $map->free(); unset($map);

  header('Location: index.php');
  exit();
}

page_header((isset($layer) ? 'Layer: '.$layer['name'] : 'New layer'));
?>
<div class="container">
  <h1>Map: <a href="index.php"><?= htmlentities($meta['name']) ?></a></h1>
  <h2><?= (isset($layer) ? 'Layer: '.htmlentities($layer['name']) : 'New layer') ?></h2>

  <form class="form-horizontal" action="layer.php<?= (isset($layer) ? '?layer='.intval($_GET['layer']) : '') ?>" method="post">
    <div class="form-group">
      <label for="inputName" class="col-sm-2 control-label">Name</label>
      <div class="col-sm-6">
        <input type="text" class="form-control" id="inputName" name="name"<?= (isset($layer) ? ' value="'.htmlentities($layer['name']).'"' : '') ?>>
      </div>
      <label for="inputGroup" class="col-sm-1 control-label">Group</label>
      <div class="col-sm-3">
        <input type="text" class="form-control" id="inputGroup" name="group"<?= (isset($layer) ? ' value="'.htmlentities($layer['group']).'"' : '') ?>>
      </div>
    </div>
    <div class="form-group">
      <label for="selectStatus" class="col-sm-2 control-label">Status</label>
      <div class="col-sm-10">
        <select class="form-control" id="selectStatus" name="status">
          <option value="<?= ($mapscript ? MS_DEFAULT : Layer::STATUS_DEFAULT) ?>"<?= (isset($layer) && $layer['status'] == ($mapscript ? MS_DEFAULT : Layer::STATUS_DEFAULT) ? ' selected="selected"' : '') ?>>DEFAULT</option>
          <option value="<?= ($mapscript ? MS_ON      : Layer::STATUS_ON     ) ?>"<?= (isset($layer) && $layer['status'] == ($mapscript ? MS_ON      : Layer::STATUS_ON     ) ? ' selected="selected"' : '') ?>>ON</option>
          <option value="<?= ($mapscript ? MS_OFF     : Layer::STATUS_OFF    ) ?>"<?= (isset($layer) && $layer['status'] == ($mapscript ? MS_OFF     : Layer::STATUS_OFF    ) ? ' selected="selected"' : '') ?>>OFF</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="selectType" class="col-sm-2 control-label">Type</label>
      <div class="col-sm-10">
        <select class="form-control" id="selectType" name="type">
<?php if ($mapscript) { ?>
          <option value="<?= MS_LAYER_CHART ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_CHART ? ' selected="selected"' : '') ?> disabled="disabled">Chart (not yet supported)</option>
          <option value="<?= MS_LAYER_CIRCLE ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_CIRCLE ? ' selected="selected"' : '') ?> disabled="disabled">Circle (not yet supported)</option>
          <option value="<?= MS_LAYER_LINE ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_LINE ? ' selected="selected"' : '') ?>>Line</option>
          <option value="<?= MS_LAYER_POINT ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_POINT ? ' selected="selected"' : '') ?>>Point</option>
          <option value="<?= MS_LAYER_POLYGON ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_POLYGON ? ' selected="selected"' : '') ?>>Polygon</option>
          <option value="<?= MS_LAYER_QUERY ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_QUERY ? ' selected="selected"' : '') ?> disabled="disabled">Query (not yet supported)</option>
          <option value="<?= MS_LAYER_RASTER ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_RASTER ? ' selected="selected"' : '') ?>>Raster</option>
          <option value="<?= MS_LAYER_TILEINDEX ?>"<?= (isset($layer) && $layer['type'] == MS_LAYER_TILEINDEX ? ' selected="selected"' : '') ?> disabled="disabled">Tile index (not yet supported)</option>
<?php } else { ?>
          <option value="<?= Layer::TYPE_CHART ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_CHART ? ' selected="selected"' : '') ?> disabled="disabled">Chart (not yet supported)</option>
          <option value="<?= Layer::TYPE_CIRCLE ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_CIRCLE ? ' selected="selected"' : '') ?> disabled="disabled">Circle (not yet supported)</option>
          <option value="<?= Layer::TYPE_LINE ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_LINE ? ' selected="selected"' : '') ?>>Line</option>
          <option value="<?= Layer::TYPE_POINT ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_POINT ? ' selected="selected"' : '') ?>>Point</option>
          <option value="<?= Layer::TYPE_POLYGON ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_POLYGON ? ' selected="selected"' : '') ?>>Polygon</option>
          <option value="<?= Layer::TYPE_QUERY ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_QUERY ? ' selected="selected"' : '') ?> disabled="disabled">Query (not yet supported)</option>
          <option value="<?= Layer::TYPE_RASTER ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_RASTER ? ' selected="selected"' : '') ?>>Raster</option>
          <option value="<?= Layer::TYPE_TILEINDEX ?>"<?= (isset($layer) && $layer['type'] == Layer::TYPE_TILEINDEX ? ' selected="selected"' : '') ?> disabled="disabled">Tile index (not yet supported)</option>
<?php } ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="selectProjection" class="col-sm-2 control-label">Projection</label>
      <div class="col-sm-10">
        <select class="form-control" id="selectProjection" name="projection">
          <option value="epsg:3857"  <?= (isset($layer) && $layer['projection'] == 'epsg:3857'   ? ' selected="selected"' : '') ?>>EPSG:3857 - Spherical Mercator</option>
          <option value="epsg:4326"  <?= (isset($layer) && $layer['projection'] == 'epsg:4326'   ? ' selected="selected"' : '') ?>>EPSG:4326 - WGS 84</option>
          <option value="epsg:31370" <?= (isset($layer) && $layer['projection'] == 'epsg:31370'  ? ' selected="selected"' : '') ?>>EPSG:31370 - Belge 1972 / Belgian Lambert 72</option>
          <option value="epsg:900913"<?= (isset($layer) && $layer['projection'] == 'epsg:900913' ? ' selected="selected"' : '') ?>>EPSG:900913 - Spherical Mercator</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="selectConnectionType" class="col-sm-2 control-label">Connection Type</label>
      <div class="col-sm-10">
        <select class="form-control" id="selectConnectionType" name="connectiontype">
<?php if ($mapscript) { ?>
          <option value="<?= MS_INLINE ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_INLINE ? ' selected="selected"' : '') ?>>Inline</option>
          <option value="<?= MS_GRATICULE ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_GRATICULE ? ' selected="selected"' : '') ?> disabled="disabled">Graticule (not yet supported)</option>
          <option value="<?= MS_OGR ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_OGR ? ' selected="selected"' : '') ?>>OGR</option>
          <option value="<?= MS_ORACLESPATIAL ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_ORACLESPATIAL ? ' selected="selected"' : '') ?> disabled="disabled">OracleSpatial (not yet supported)</option>
          <option value="<?= MS_PLUGIN ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_PLUGIN ? ' selected="selected"' : '') ?> disabled="disabled">Plugin (not yet supported)</option>
          <option value="<?= MS_POSTGIS ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_POSTGIS ? ' selected="selected"' : '') ?> disabled="disabled">PostGIS (not yet supported)</option>
          <option value="<?= MS_RASTER ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_RASTER ? ' selected="selected"' : '') ?> disabled="disabled">Raster (not yet supported)</option>
          <option value="<?= MS_SDE ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_SDE ? ' selected="selected"' : '') ?> disabled="disabled">SDE (not yet supported)</option>
          <option value="<?= MS_SHAPEFILE ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_SHAPEFILE ? ' selected="selected"' : '') ?>>Shapefile</option>
          <?php /*<option value="<?= MS_TILED_OGR ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_TILED_OGR ? ' selected="selected"' : '') ?>>Tiled OGR</option> */ ?>
          <option value="<?= MS_TILED_SHAPEFILE ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_TILED_SHAPEFILE ? ' selected="selected"' : '') ?>>Tiled shapefile</option>
          <option value="<?= MS_UNION ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_UNION ? ' selected="selected"' : '') ?> disabled="disabled">Union (not yet supported)</option>
          <option value="<?= MS_WFS ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_WFS ? ' selected="selected"' : '') ?>>WFS</option>
          <option value="<?= MS_WMS ?>"<?= (isset($layer) && $layer['connectiontype'] == MS_WMS ? ' selected="selected"' : '') ?>>WMS</option>
<?php } else { ?>
          <option value="<?= Layer::CONNECTIONTYPE_CONTOUR ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_CONTOUR ? ' selected="selected"' : '') ?> disabled="disabled">Contour (not yet supported)</option>
          <option value="<?= Layer::CONNECTIONTYPE_LOCAL ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_LOCAL ? ' selected="selected"' : '') ?>>Local</option>
          <option value="<?= Layer::CONNECTIONTYPE_OGR ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_OGR ? ' selected="selected"' : '') ?>>OGR</option>
          <option value="<?= Layer::CONNECTIONTYPE_ORACLESPATIAL ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_ORACLESPATIAL ? ' selected="selected"' : '') ?> disabled="disabled">OracleSpatial (not yet supported)</option>
          <option value="<?= Layer::CONNECTIONTYPE_PLUGIN ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_PLUGIN ? ' selected="selected"' : '') ?> disabled="disabled">Plugin (not yet supported)</option>
          <option value="<?= Layer::CONNECTIONTYPE_POSTGIS ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_POSTGIS ? ' selected="selected"' : '') ?> disabled="disabled">PostGIS (not yet supported)</option>
          <option value="<?= Layer::CONNECTIONTYPE_SDE ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_CONTOUR ? ' selected="selected"' : '') ?> disabled="disabled">SDE (not yet supported)</option>
          <option value="<?= Layer::CONNECTIONTYPE_UNION ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_SDE ? ' selected="selected"' : '') ?> disabled="disabled">Union (not yet supported)</option>
          <option value="<?= Layer::CONNECTIONTYPE_UVRASTER ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_UVRASTER ? ' selected="selected"' : '') ?> disabled="disabled">UV Raster (not yet supported)</option>
          <option value="<?= Layer::CONNECTIONTYPE_WFS ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_WFS ? ' selected="selected"' : '') ?>>WFS</option>
          <option value="<?= Layer::CONNECTIONTYPE_WMS ?>"<?= (isset($layer) && $layer['connectiontype'] == Layer::CONNECTIONTYPE_WMS ? ' selected="selected"' : '') ?>>WMS</option>
<?php } ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="inputConnection" class="col-sm-2 control-label">Connection</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="inputConnection" name="connection"<?= (isset($layer) ? ' value="'.htmlentities($layer['connection']).'"' : '') ?>>
      </div>
    </div>
    <div class="form-group">
      <label for="inputData" class="col-sm-2 control-label">Data</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="inputData" name="data"<?= (isset($layer) ? ' value="'.htmlentities($layer['data']).'"' : '') ?>>
      </div>
    </div>
    <div class="form-group">
      <label for="inputFilterItem" class="col-sm-2 control-label">Filter Item</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="inputFilterItem" name="filteritem"<?= (isset($layer) ? ' value="'.htmlentities($layer['filteritem']).'"' : '') ?>>
      </div>
    </div>
    <div class="form-group">
      <label for="inputFilter" class="col-sm-2 control-label">Filter</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="inputFilter" name="filter"<?= (isset($layer) ? ' value="'.htmlentities($layer['filter']).'"' : '') ?>>
      </div>
    </div>
    <div class="form-group text-center">
      <button type="submit" class="btn btn-primary" name="action" value="save"><i class="fa fa-floppy-o"></i> Save</button>
      <a href="index.php" class="btn btn-default"><i class="fa fa-backward"></i> Cancel</a>
    </div>
  </form>

</div>
<?php
page_footer();