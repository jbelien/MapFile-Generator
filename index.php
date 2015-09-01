<?php
require_once('fn.php');

require_once('library/map.php');

use MapFile\Map;
use MapFile\Layer;

session_start();

$tmp = sys_get_temp_dir();
if (!file_exists($tmp.'/mapserver') || !is_dir($tmp.'/mapserver')) mkdir($tmp.'/mapserver');

$settings = parse_ini_file('settings.ini');
$mapscript = extension_loaded('mapscript');

if (isset($_GET['map']) && file_exists($_GET['map'])) {
  $mapfile = $tmp.'/mapserver/mapfile-'.uniqid().'.map';
  $_SESSION['mapfile-generator']['mapfile'] = $mapfile;

  if ($mapscript) {
    $_SESSION['mapfile-generator']['source'] = $_GET['map'];

    try {
      $_map = new mapObj($_GET['map']);
      $_map->save($_SESSION['mapfile-generator']['mapfile']);
      $_map->free(); unset($_map);
    } catch (MapScriptException $e) {
      $error = $e->getMessage();
    }
  }
  else if (isset($_GET['map'])) {
    $_SESSION['mapfile-generator']['source'] = $_GET['map'];

    try {
      $_map = new Map($_GET['map']);
      $_map->save($_SESSION['mapfile-generator']['mapfile']);
    } catch (Exception $e) {
      $error = $e->getMessage();
    }
  }
}
else if (!isset($_SESSION['mapfile-generator']['mapfile'])) {
  $mapfile = $tmp.'/mapserver/mapfile-'.uniqid().'.map';
  $_SESSION['mapfile-generator']['mapfile'] = $mapfile;

  unset($_SESSION['mapfile-generator']['source']);

  if ($mapscript) {
    try {
      $_map = new mapObj(NULL);

      $map->setProjection('epsg:4326', MS_TRUE);
      $map->setExtent(-180,-90,180,90);
      $map->setSize(500, 500);

      $_map->save($_SESSION['mapfile-generator']['mapfile']);
      $_map->free(); unset($_map);
    } catch (MapScriptException $e) {
      $error = $e->getMessage();
    }
  }
  else {
  }
}

if (!isset($error) && $mapscript) {
  $map = new mapObj($_SESSION['mapfile-generator']['mapfile']);

  $map->setFontSet($settings['fontset']);
  $map->setSymbolSet($settings['symbolset']);

  $map->legend->label->type = MS_TRUETYPE;
  $map->legend->label->font = $settings['font'];
  $map->legend->label->size = 8.0;

  $map->scalebar->label->type = MS_TRUETYPE;
  $map->scalebar->label->font = $settings['font'];
  $map->scalebar->label->size = 8.0;
  $map->scalebar->units = MS_KILOMETERS;
  $map->scalebar->color->setRGB(0,0,0);
  $map->scalebar->outlinecolor->setRGB(0,0,0);

  if (isset($_GET['up'])) $map->moveLayerUp(intval($_GET['up']));
  else if (isset($_GET['down'])) $map->moveLayerDown(intval($_GET['down']));
  else if (isset($_GET['remove'])) $map->removeLayer(intval($_GET['remove']));
  else if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $map->name = trim($_POST['name']);
    $map->setProjection($_POST['projection'], MS_FALSE);
    $map->setExtent($_POST['extentminx'], $_POST['extentminy'], $_POST['extentmaxx'], $_POST['extentmaxy']);

    if (isset($_POST['wms']) && $_POST['wms'] == 1) {
      $map->setMetaData('wms_enable_request', '*');
      $map->setMetaData('wms_feature_info_mime_type', 'text/plain application/vnd.ogc.gml');
      $map->setMetaData('wms_srs', 'EPSG:31370 EPSG:4326 EPSG:3857');
      $map->setMetaData('wms_title', $_POST['wms_title']);
      $map->setMetaData('wms_abstract', $_POST['wms_abstract']);
      $map->setMetaData('wms_attribution_title', $_POST['wms_attribution_title']);
      $map->setMetaData('wms_attribution_onlineresource', $_POST['wms_attribution_onlineresource']);
      //$map->setMetaData('wms_encoding', '');
    } else {
      if (strlen($map->getMetaData('wms_enable_request')) > 0) $map->removeMetaData('wms_enable_request');
      if (strlen($map->getMetaData('wms_feature_info_mime_type')) > 0) $map->removeMetaData('wms_feature_info_mime_type');
      if (strlen($map->getMetaData('wms_srs')) > 0) $map->removeMetaData('wms_srs');
      if (strlen($map->getMetaData('wms_title')) > 0) $map->removeMetaData('wms_title');
      if (strlen($map->getMetaData('wms_abstract')) > 0) $map->removeMetaData('wms_abstract');
      if (strlen($map->getMetaData('wms_attribution_title')) > 0) $map->removeMetaData('wms_attribution_title');
      if (strlen($map->getMetaData('wms_attribution_onlineresource')) > 0) $map->removeMetaData('wms_attribution_onlineresource');
      //$map->removeMetaData('wms_encoding');
    }
  }

  $map->save($_SESSION['mapfile-generator']['mapfile']);
  $map->free(); unset($map);
} else {
}

$meta = mapfile_getmeta($_SESSION['mapfile-generator']['mapfile']);
$layers = mapfile_getlayers($_SESSION['mapfile-generator']['mapfile']);

page_header();

if (isset($error)) echo '<div class="alert alert-danger" role="alert"><strong>Error :</strong> '.htmlentities($error).'</div>';
?>
<div class="container">

  <form action="index.php" method="post" autocomplete="off">
    <div class="row">
      <div class="form-group form-group-lg col-sm-6">
        <label for="inputName">Map name</label>
        <input type="text" class="form-control" id="inputName" name="name" value="<?= $meta['name'] ?>" required="required">
      </div>
      <div class="form-group form-group-lg col-sm-6">
        <label for="selectProj">Map projection</label>
        <select class="form-control" id="selectProj" name="projection" required="required">
          <option value="epsg:3857" data-minx="-20026376.39" data-miny="-20048966.10" data-maxx="20026376.39" data-maxy="20048966.10"<?= (isset($meta) && $meta['projection'] == 'epsg:3857' ? ' selected="selected"' : '') ?>>EPSG:3857 - Spherical Mercator</option>
          <option value="epsg:4326" data-minx="-180.0" data-miny="-90.0" data-maxx="180.0" data-maxy="90.0"<?= (isset($meta) && $meta['projection'] == 'epsg:4326' ? ' selected="selected"' : '') ?>>EPSG:4326 - WGS 84</option>
          <option value="epsg:31370" data-minx="0" data-miny="0" data-maxx="300000" data-maxy="300000"<?= (isset($meta) && $meta['projection'] == 'epsg:31370' ? ' selected="selected"' : '') ?>>EPSG:31370 - Belge 1972 / Belgian Lambert 72</option>
          <!--<option value="epsg:900913" data-minx="-20026376.39" data-miny="-20048966.10" data-maxx="20026376.39" data-maxy="20048966.10"<?= (isset($meta) && $meta['projection'] == 'epsg:900913' ? ' selected="selected"' : '') ?>>EPSG:900913 - Spherical Mercator</option>-->
        </select>
      </div>
    </div>
    <div class="row">
      <div class="form-group form-group-sm col-sm-3">
        <label for="inputExtentMinX">Map extent : MIN X</label>
        <input type="text" class="form-control" id="inputExtentMinX" name="extentminx" value="<?= $meta['extent'][0] ?>" required="required">
      </div>
      <div class="form-group form-group-sm col-sm-3">
        <label for="inputExtentMinY">Map extent : MIN Y</label>
        <input type="text" class="form-control" id="inputExtentMinY" name="extentminy" value="<?= $meta['extent'][1] ?>" required="required">
      </div>
      <div class="form-group form-group-sm col-sm-3">
        <label for="inputExtentMaxX">Map extent : MAX X</label>
        <input type="text" class="form-control" id="inputExtentMaxX" name="extentmaxx" value="<?= $meta['extent'][2] ?>" required="required">
      </div>
      <div class="form-group form-group-sm col-sm-3">
        <label for="inputExtentMaxY">Map extent : MAX Y</label>
        <input type="text" class="form-control" id="inputExtentMaxY" name="extentmaxy" value="<?= $meta['extent'][3] ?>" required="required">
      </div>
    </div>
    <div>
      <div class="checkbox"><label><input type="checkbox" name="wms" value="1"<?= ($meta['wms'] ? ' checked="checked"' : '') ?>> Enable WMS</label></div>
      <div class="form-horizontal wms-control"<?= (!$meta['wms'] ? ' style="display:none;"' : '') ?>>
        <div class="form-group">
          <label for="inputWMSTitle" class="col-sm-3 control-label">WMS Title</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="inputWMSTitle" name="wms_title" value="<?= (isset($meta['wmstitle']) ? $meta['wmstitle'] : '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="inputWMSAbstract" class="col-sm-3 control-label">WMS Abstract</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="inputWMSAbstract" name="wms_abstract" value="<?= (isset($meta['wmsabstract']) ? $meta['wmsabstract'] : '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="inputWMSAttributionTitle" class="col-sm-3 control-label">Attribution title</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="inputWMSAttributionTitle" name="wms_attribution_title" value="<?= (isset($meta['wmsattributiontitle']) ? $meta['wmsattributiontitle'] : '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="inputWMSAttributionOnlineResource" class="col-sm-3 control-label">Attribution online resource</label>
          <div class="col-sm-9">
            <input type="url" class="form-control" id="inputWMSAttributionOnlineResource" name="wms_attribution_onlineresource" value="<?= (isset($meta['wmsattributiononlineresource']) ? $meta['wmsattributiononlineresource'] : '') ?>">
          </div>
        </div>
        <!--
        <div class="form-group">
          <label for="inputWMSEncoding" class="col-sm-2 control-label">WMS Encoding</label>
          <div class="col-sm-10">
            <select class="form-control" id="selectWMSEncoding">
              <option value="ISO-8859-1">ISO-8859-1 (Latin 1)</option>
              <option value="UTF-8">UTF-8</option>
            </select>
          </div>
        </div>
        -->
      </div>
    </div>
    <div class="form-group text-center">
      <button type="submit" class="btn btn-primary" name="action" value="save"><i class="fa fa-floppy-o"></i> Save</button>
    </div>
  </form>

  <hr>

  <h2>Layers</h2>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Group</th>
        <th>Name</th>
        <th>Type</th>
        <th>Projection</th>
        <th>Status</th>
        <th colspan="5"></th>
      </tr>
    </thead>
    <tbody>
<?php
    foreach ($layers as $k => $data) {
      echo '<tr>';
        echo '<td>'.htmlentities($data['group']).'</td>';
        echo '<th>'.htmlentities($data['name']).'</th>';
        echo '<td>';
          switch($data['type']) {
            case ($mapscript ? MS_LAYER_CHART     : Layer::TYPE_CHART    ): echo 'Chart'; break;
            case ($mapscript ? MS_LAYER_CIRCLE    : Layer::TYPE_CIRCLE   ): echo 'Circle'; break;
            case ($mapscript ? MS_LAYER_LINE      : Layer::TYPE_LINE     ): echo 'Line'; break;
            case ($mapscript ? MS_LAYER_POINT     : Layer::TYPE_POINT    ): echo 'Point'; break;
            case ($mapscript ? MS_LAYER_POLYGON   : Layer::TYPE_POLYGON  ): echo 'Polygon'; break;
            case ($mapscript ? MS_LAYER_QUERY     : Layer::TYPE_QUERY    ): echo 'Query'; break;
            case ($mapscript ? MS_LAYER_RASTER    : Layer::TYPE_RASTER   ): echo 'Raster'; break;
            case ($mapscript ? MS_LAYER_TILEINDEX : Layer::TYPE_TILEINDEX): echo 'TileIndex'; break;
            default: echo '<i class="text-warning">Unkown</i>'; break;
          }
        echo '</td>';
        echo '<td>'.htmlentities(strtoupper($data['projection'])).'</td>';
        echo '<td>';
          switch($data['status']) {
            case ($mapscript ? MS_ON      : Layer::STATUS_ON     ): echo '<i class="fa fa-check"></i> ON'; break;
            case ($mapscript ? MS_OFF     : Layer::STATUS_OFF    ): echo '<i class="fa fa-remove"></i> OFF'; break;
            case ($mapscript ? MS_DEFAULT : Layer::STATUS_DEFAULT): echo '<i class="fa fa-check"></i> DEFAULT'; break;
            default: echo '<i class="text-warning">Unkown</i>'; break;
          }
        echo '</td>';
        echo '<td style="width:75px;"><a style="text-decoration:none;" href="layer.php?layer='.$k.'"><i class="fa fa-database"></i> Data</a></td>';
        echo '<td style="width:150px;"><a style="text-decoration:none;" href="layer-class.php?layer='.$k.'"><i class="fa fa-paint-brush"></i> Styles &amp; Labels</a></td>';
        echo '<td style="width:20px; text-align:center;">'.($k < (count($layers)-1) ? '<a href="?down='.$k.'" title="Move down"><i class="fa fa-arrow-down"></i></a>' : '').'</td>';
        echo '<td style="width:20px; text-align:center;">'.($k > 0 ? '<a href="?up='.$k.'" title="Move up"><i class="fa fa-arrow-up"></i></a>' : '').'</td>';
        echo '<td style="width:20px; text-align:center;"><a href="?remove='.$k.'" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
      echo '</tr>';
    }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="10" class="text-right"><a href="layer.php" style="text-decoration:none;"><i class="fa fa-plus-square"></i> Add new layer</a></td>
      </tr>
    </tfoot>
  </table>

</div>

<script>
  var mapfile = '<?= $_SESSION['mapfile-generator']['mapfile'] ?>';
  var mapscript = <?= ($mapscript ? 'true' : 'false') ?>;

  $(document).ready(function() {
    $('a.text-danger').on('click', function(event) { if (!confirm('Are you sure you want to delete this layer ?')) { event.preventDefault(); } });

    $('input[name=wms]').on('click', function() { if ($(this).prop('checked') == true) $('.wms-control').show(); else { $('.wms-control').hide(); $('.wms-control input').val(''); } });

    $('#selectProj').on('change', function() {
      var data = $(this).find('option:selected').data();
      if (typeof(data.minx) != 'undefined' && typeof(data.miny) != 'undefined' && typeof(data.maxx) != 'undefined' && typeof(data.maxy) != 'undefined') {
        $('#inputExtentMinX').val(data.minx); $('#inputExtentMinY').val(data.miny);
        $('#inputExtentMaxX').val(data.maxx); $('#inputExtentMaxY').val(data.maxy);
      }
    });
  });
</script>

<?php
page_footer();