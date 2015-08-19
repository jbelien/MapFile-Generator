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

if (isset($_GET['save'], $_SESSION['mapfile-generator']['mapfile'], $_SESSION['mapfile-generator']['source'])) {
  copy($_SESSION['mapfile-generator']['source'], $_SESSION['mapfile-generator']['source'].'.bak');
  copy($_SESSION['mapfile-generator']['mapfile'], $_SESSION['mapfile-generator']['source']);
}
if (isset($_GET['export'], $_SESSION['mapfile-generator']['mapfile'])) {
  $fsize = filesize($_SESSION['mapfile-generator']['mapfile']); $fname = (isset($_SESSION['mapfile-generator']['source']) ? basename($_SESSION['mapfile-generator']['source']) : basename($_SESSION['mapfile-generator']['mapfile']));
  header('Content-Type: application/force-download; name="'.$fname.'"');
  header('Content-Transfer-Encoding: binary');
  header('Content-Length: '.$fsize);
  header('Content-Disposition: attachment; filename="'.$fname.'"');
  header('Expires: 0');
  header('Cache-Control: no-cache, must-revalidate');
  header('Pragma: no-cache');
  readfile($_SESSION['mapfile-generator']['mapfile']);
  exit();
}

if (!isset($_SESSION['mapfile-generator']['mapfile'])) {
  $mapfile = $tmp.'/mapserver/mapfile-'.uniqid().'.map';
  $_SESSION['mapfile-generator']['mapfile'] = $mapfile;
}

if ($mapscript) {
  if (isset($_GET['map']) && file_exists($_GET['map'])) {
    $_SESSION['mapfile-generator']['source'] = $_GET['map'];

    try {
      $_map = new mapObj($_GET['map']);
      $_map->save($_SESSION['mapfile-generator']['mapfile']);
      $_map->free(); unset($_map);
    } catch (MapScriptException $e) {
      $error = $e->getMessage();
    }
  } else {
    unset($_SESSION['mapfile-generator']['source']);
    $_map = new mapObj(NULL); $_map->save($_SESSION['mapfile-generator']['mapfile']); $_map->free(); unset($_map);
  }
} else {
  if (isset($_GET['map']) && file_exists($_GET['map'])) {
    $_SESSION['mapfile-generator']['source'] = $_GET['map'];

    try {
      $_map = new Map($_GET['map']);
      $_map->save($_SESSION['mapfile-generator']['mapfile']);
    } catch (Exception $e) {
      $error = $e->getMessage();
    }
  } else {
    unset($_SESSION['mapfile-generator']['source']);
    $_map = new Map(); $_map->save($_SESSION['mapfile-generator']['mapfile']);
  }
}

$meta = mapfile_getmeta($_SESSION['mapfile-generator']['mapfile']);
$layers = mapfile_getlayers($_SESSION['mapfile-generator']['mapfile']);

page_header();

if (isset($error)) echo '<div class="alert alert-danger" role="alert"><strong>Error :</strong> '.htmlentities($error).'</div>';
?>
      <div role="tabpanel">

        <!-- Tab panes -->
        <div class="tab-content">

          <div role="tabpanel" class="tab-pane active" id="editor">
            <form autocomplete="off">
              <div class="row">
                <div class="form-group form-group-lg col-sm-6">
                  <label for="inputName">Map name</label>
                  <input type="text" class="form-control" id="inputName" name="name" value="<?= $meta['name'] ?>" required="required">
                </div>
                <div class="form-group form-group-lg col-sm-6">
                  <label for="selectProj">Map projection</label>
                  <select class="form-control" id="selectProj" name="projection">
                    <option value="epsg:3857" data-minx="-20026376.39" data-miny="-20048966.10" data-maxx="20026376.39" data-maxy="20048966.10"<?= ($meta['projection'] == 'epsg:3857' ? ' selected="selected"' : '') ?>>EPSG:3857 - Spherical Mercator</option>
                    <option value="epsg:4326" data-minx="-180.0" data-miny="-90.0" data-maxx="180.0" data-maxy="90.0"<?= ($meta['projection'] == 'epsg:4326' ? ' selected="selected"' : '') ?>>EPSG:4326 - WGS 84</option>
                    <option value="epsg:31370" data-minx="0" data-miny="0" data-maxx="300000" data-maxy="300000"<?= ($meta['projection'] == 'epsg:31370' ? ' selected="selected"' : '') ?>>EPSG:31370 - Belge 1972 / Belgian Lambert 72</option>
                    <option value="epsg:900913" data-minx="-20026376.39" data-miny="-20048966.10" data-maxx="20026376.39" data-maxy="20048966.10"<?= ($meta['projection'] == 'epsg:900913' ? ' selected="selected"' : '') ?>>EPSG:900913 - Spherical Mercator</option>
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
                      <input type="text" class="form-control" id="inputWMSAttributionTitle" name="wms_attribution_title">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputWMSAttributionOnlineResource" class="col-sm-3 control-label">Attribution online resource</label>
                    <div class="col-sm-9">
                      <input type="url" class="form-control" id="inputWMSAttributionOnlineResource" name="wms_attribution_onlineresource">
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
              <hr>
              <div id="layers" class="form-horizontal">
                <h2>Layers</h2>
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Type</th>
                      <th>Projection</th>
                      <!--<th>Data</th>-->
                      <th>Status</th>
                      <th colspan="5"></th>
                    </tr>
                  </thead>
                  <tbody>
<?php
                  foreach ($layers as $k => $data) {
                    echo '<tr>';
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
                      echo '<!--<td>';
                        if (!empty($data['data'])) echo htmlentities($data['data']);
                        else echo htmlentities($data['connection']);
                      echo '</td>-->';
                      echo '<td>';
                        switch($data['status']) {
                          case ($mapscript ? MS_ON      : Layer::STATUS_ON     ): echo '<i class="fa fa-check"></i> ON'; break;
                          case ($mapscript ? MS_OFF     : Layer::STATUS_OFF    ): echo '<i class="fa fa-remove"></i> OFF'; break;
                          case ($mapscript ? MS_DEFAULT : Layer::STATUS_DEFAULT): echo '<i class="fa fa-check"></i> DEFAULT'; break;
                          default: echo '<i class="text-warning">Unkown</i>'; break;
                        }
                      echo '</td>';
                      echo '<td style="width:75px;"><a style="text-decoration:none;" href="layer.php?map='.urlencode($_SESSION['mapfile-generator']['source']).'&amp;layer='.$k.'"><i class="fa fa-database"></i> Data</a></td>';
                      echo '<td style="width:150px;"><a style="text-decoration:none;" href="layer-class.php?map='.urlencode($_SESSION['mapfile-generator']['source']).'&amp;layer='.$k.'"><i class="fa fa-paint-brush"></i> Style &amp; Label</a></td>';
                      echo '<td style="width:20px; text-align:center;">'.($k < (count($layers)-1) ? '<a href="#" title="Move down"><i class="fa fa-arrow-down"></i></a>' : '').'</td>';
                      echo '<td style="width:20px; text-align:center;">'.($k > 0 ? '<a href="#" title="Move up"><i class="fa fa-arrow-up"></i></a>' : '').'</td>';
                      echo '<td style="width:20px; text-align:center;"><a href="#" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
                    echo '</tr>';
                  }
?>
                  </tbody>
                </table>

                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th class="col-sm-8">Name</th>
                      <th class="col-sm-3">Type</th>
                      <th class="col-sm-1"></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  foreach ($layers as $k => $data) {
                  ?>
                    <tr class="layer">
                      <td>
                        <input type="text" class="form-control" name="layer_name" value="<?= $data['name'] ?>" required="required">
                      </td>
                      <td>
                        <select class="form-control" name="layer_type">
                          <?php if ($mapscript) { ?>
                          <option value="<?= MS_LAYER_CHART ?>"<?= ($data['type'] == MS_LAYER_CHART ? ' selected="selected"' : '') ?> disabled="disabled">Chart (not yet supported)</option>
                          <option value="<?= MS_LAYER_CIRCLE ?>"<?= ($data['type'] == MS_LAYER_CIRCLE ? ' selected="selected"' : '') ?> disabled="disabled">Circle (not yet supported)</option>
                          <option value="<?= MS_LAYER_LINE ?>"<?= ($data['type'] == MS_LAYER_LINE ? ' selected="selected"' : '') ?>>Line</option>
                          <option value="<?= MS_LAYER_POINT ?>"<?= ($data['type'] == MS_LAYER_POINT ? ' selected="selected"' : '') ?>>Point</option>
                          <option value="<?= MS_LAYER_POLYGON ?>"<?= ($data['type'] == MS_LAYER_POLYGON ? ' selected="selected"' : '') ?>>Polygon</option>
                          <option value="<?= MS_LAYER_QUERY ?>"<?= ($data['type'] == MS_LAYER_QUERY ? ' selected="selected"' : '') ?> disabled="disabled">Query (not yet supported)</option>
                          <option value="<?= MS_LAYER_RASTER ?>"<?= ($data['type'] == MS_LAYER_RASTER ? ' selected="selected"' : '') ?>>Raster</option>
                          <option value="<?= MS_LAYER_TILEINDEX ?>"<?= ($data['type'] == MS_LAYER_TILEINDEX ? ' selected="selected"' : '') ?> disabled="disabled">Tile index (not yet supported)</option>
                          <?php } else { ?>
                          <option value="<?= Layer::TYPE_CHART ?>"<?= ($data['type'] == Layer::TYPE_CHART ? ' selected="selected"' : '') ?> disabled="disabled">Chart (not yet supported)</option>
                          <option value="<?= Layer::TYPE_CIRCLE ?>"<?= ($data['type'] == Layer::TYPE_CIRCLE ? ' selected="selected"' : '') ?> disabled="disabled">Circle (not yet supported)</option>
                          <option value="<?= Layer::TYPE_LINE ?>"<?= ($data['type'] == Layer::TYPE_LINE ? ' selected="selected"' : '') ?>>Line</option>
                          <option value="<?= Layer::TYPE_POINT ?>"<?= ($data['type'] == Layer::TYPE_POINT ? ' selected="selected"' : '') ?>>Point</option>
                          <option value="<?= Layer::TYPE_POLYGON ?>"<?= ($data['type'] == Layer::TYPE_POLYGON ? ' selected="selected"' : '') ?>>Polygon</option>
                          <option value="<?= Layer::TYPE_QUERY ?>"<?= ($data['type'] == Layer::TYPE_QUERY ? ' selected="selected"' : '') ?> disabled="disabled">Query (not yet supported)</option>
                          <option value="<?= Layer::TYPE_RASTER ?>"<?= ($data['type'] == Layer::TYPE_RASTER ? ' selected="selected"' : '') ?>>Raster</option>
                          <option value="<?= Layer::TYPE_TILEINDEX ?>"<?= ($data['type'] == Layer::TYPE_TILEINDEX ? ' selected="selected"' : '') ?> disabled="disabled">Tile index (not yet supported)</option>
                          <?php } ?>
                        </select>
                      </td>
                      <td class="text-center">
                        <div class="btn-group">
                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-cogs"></i> <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="#modal-data" data-toggle="modal"><i class="fa fa-database"></i> Data</a></li>
                            <li><a href="#modal-class" data-toggle="modal"><i class="fa fa-paint-brush"></i> Label &amp; Style</a></li>
                            <li class="wms-control"<?= (!$meta['wms'] ? ' style="display:none;"' : '') ?>><a href="#modal-wms" data-toggle="modal"><i class="fa fa-globe"></i> WMS</a></li>
                            <li role="presentation" class="divider"></li>
                            <li><a href="#move-up"><i class="fa fa-arrow-up"></i> Move up</a></li>
                            <li><a href="#move-down"><i class="fa fa-arrow-down"></i> Move down</a></li>
                            <li><a href="#delete"><i class="fa fa-trash-o"></i> Delete</a></li>
                            <!--<li><a href="#duplicate"><i class="fa fa-files-o"></i> Duplicate</a></li>-->
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php
                  }

                  $count = count($layers);
                  ?>
                    <tr class="layer">
                      <td>
                        <input type="text" class="form-control" name="layer_name" required="required">
                      </td>
                      <td>
                        <select class="form-control" name="layer_type">
                          <?php if ($mapscript) { ?>
                          <option value="<?= MS_LAYER_CHART ?>" disabled="disabled">Chart (not yet supported)</option>
                          <option value="<?= MS_LAYER_CIRCLE ?>" disabled="disabled">Circle (not yet supported)</option>
                          <option value="<?= MS_LAYER_LINE ?>">Line</option>
                          <option value="<?= MS_LAYER_POINT ?>">Point</option>
                          <option value="<?= MS_LAYER_POLYGON ?>">Polygon</option>
                          <option value="<?= MS_LAYER_QUERY ?>" disabled="disabled">Query (not yet supported)</option>
                          <option value="<?= MS_LAYER_RASTER ?>">Raster</option>
                          <option value="<?= MS_LAYER_TILEINDEX ?>" disabled="disabled">Tile index (not yet supported)</option>
                          <?php } else { ?>
                          <option value="<?= Layer::TYPE_CHART ?>" disabled="disabled">Chart (not yet supported)</option>
                          <option value="<?= Layer::TYPE_CIRCLE ?>" disabled="disabled">Circle (not yet supported)</option>
                          <option value="<?= Layer::TYPE_LINE ?>">Line</option>
                          <option value="<?= Layer::TYPE_POINT ?>">Point</option>
                          <option value="<?= Layer::TYPE_POLYGON ?>">Polygon</option>
                          <option value="<?= Layer::TYPE_QUERY ?>" disabled="disabled">Query (not yet supported)</option>
                          <option value="<?= Layer::TYPE_RASTER ?>">Raster</option>
                          <option value="<?= Layer::TYPE_TILEINDEX ?>" disabled="disabled">Tile index (not yet supported)</option>
                          <?php } ?>
                        </select>
                      </td>
                      <td class="text-center">
                        <div class="btn-group">
                          <button type="button" class="btn btn-default dropdown-toggle disabled" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-cogs"></i> <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="#modal-data" data-toggle="modal"><i class="fa fa-database"></i> Data</a></li>
                            <li><a href="#modal-class" data-toggle="modal"><i class="fa fa-paint-brush"></i> Label &amp; Style</a></li>
                            <li class="wms-control"<?= (!$meta['wms'] ? ' style="display:none;"' : '') ?>><a href="#modal-wms" data-toggle="modal"><i class="fa fa-globe"></i> WMS</a></li>
                            <li role="presentation" class="divider"></li>
                            <li><a href="#move-up"><i class="fa fa-arrow-up"></i> Move up</a></li>
                            <li><a href="#move-down"><i class="fa fa-arrow-down"></i> Move down</a></li>
                            <li><a href="#delete"><i class="fa fa-trash-o"></i> Delete</a></li>
                            <!--<li><a href="#duplicate"><i class="fa fa-files-o"></i> Duplicate</a></li>-->
                          </ul>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </form>
          </div>

          <div role="tabpanel" class="tab-pane" id="mapfile">
            <div class="text-center">
              <button id="mapfile-open" class="btn btn-default" data-toggle="modal" data-target="#modal-open"><i class="fa fa-folder-open-o"></i> Open</button>
              <?php if (isset($_SESSION['mapfile-generator']['source'])) echo '<a id="mapfile-save" class="btn btn-default" href="?map='.$_SESSION['mapfile-generator']['source'].'&amp;save"><i class="fa fa-floppy-o"></i> Save</a>'; ?>
              <a id="mapfile-export" class="btn btn-default" href="?export"><i class="fa fa-download"></i> Export</a>
            </div>
            <?php if (isset($_SESSION['mapfile-generator']['source'])) echo '<p class="text-info">Source : <samp>'.$_SESSION['mapfile-generator']['source'].'</samp></p>'; ?>
            <p class="text-info">Last update : -</p>
            <pre><?= file_get_contents($_SESSION['mapfile-generator']['mapfile']) ?></pre>
          </div>

          <div role="tabpanel" class="tab-pane row" id="map" data-url="<?= $settings['mapserv'] ?>?map=<?= $_SESSION['mapfile-generator']['mapfile'] ?>">
            <div class="col-sm-7">
              <div class="panel panel-default">
                <div class="panel-body text-center"><img alt="Unable to draw the map." src="<?= $settings['mapserv'] ?>?map=<?= $_SESSION['mapfile-generator']['mapfile'] ?>&amp;mode=map&amp;layers=all&amp;<?= time() ?>"></div>
              </div>
            </div>
            <div class="col-sm-5">
              <div class="panel panel-default">
                <div class="panel-heading">Scalebar:</div>
                <div class="panel-body"><img class="img-responsive" alt="Unable to draw the scalebar." id="map-scalebar" src="<?= $settings['mapserv'] ?>?map=<?= $_SESSION['mapfile-generator']['mapfile'] ?>&amp;mode=scalebar&amp;layers=all&amp;<?= time() ?>"></div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading">Legend:</div>
                <div class="panel-body"><img class="img-responsive" alt="Unable to draw the legend." id="map-legend" src="<?= $settings['mapserv'] ?>?map=<?= $_SESSION['mapfile-generator']['mapfile'] ?>&amp;mode=legend&amp;layers=all&amp;<?= time() ?>"></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div id="modal-open" class="modal fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h3 class="modal-title"><i class="fa fa-folder-open-o"></i> Open</h3>
          </div>
          <div class="modal-body">
          <?php
          $dir = $settings['directory'];

          $dh = opendir($dir);
            while (($file = readdir($dh)) !== FALSE) {
              if (in_array($file, array('.', '..'))) continue;

              $ext = pathinfo($file, PATHINFO_EXTENSION);
              if (strtolower($ext) != 'map') continue;

              echo '<a href="?map='.$dir.'/'.$file.'">'.$file.'</a><br>';
            }
          closedir($dh);
          ?>
          </div>
        </div>
      </div>
    </div>

    <?php
    require_once('modal-data.php');
    require_once('modal-class.php');
    require_once('modal-label.php');
    require_once('modal-style.php');
    require_once('modal-wms.php');
    ?>

    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.11.2.min.js"><\/script>')</script>
    <script src="js/jquery.serialize-object.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script>
      var mapfile = '<?= $_SESSION['mapfile-generator']['mapfile'] ?>';
      var mapscript = <?= ($mapscript ? 'true' : 'false') ?>;
      <?php echo '$(document).ready(function(){'; foreach($layers as $k => $data) { echo "$('.layer:eq(".$k.")').data(".json_encode($json).");"; } echo '});'.PHP_EOL; ?>
    </script>
    <script src="js/main.js"></script>
    <script src="js/modal-data.js"></script>
    <script src="js/modal-class.js"></script>
    <script src="js/modal-label.js"></script>
    <script src="js/modal-style.js"></script>
    <script src="js/modal-wms.js"></script>
  </body>
</html>