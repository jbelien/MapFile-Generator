<?php
require_once('library/map.php');

use MapFile\Map;
use MapFile\Layer;

session_start();

$tmp = sys_get_temp_dir();
if (!file_exists($tmp.'/mapserver') || !is_dir($tmp.'/mapserver')) mkdir($tmp.'/mapserver');

$settings = parse_ini_file('settings.ini');
$mapscript = extension_loaded('mapscript');

if (isset($_GET['save'], $_SESSION['mapfile-generator']['mapfile'], $_SESSION['mapfile-generator']['source'])) {
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


if ($mapscript) {
  $map = new mapObj($_SESSION['mapfile-generator']['mapfile']);

  $map_name = $map->name;
  $map_extent = array($map->extent->minx, $map->extent->miny, $map->extent->maxx, $map->extent->maxy);
  if (preg_match('/(epsg:[0-9]+)/i', $map->getProjection(), $_p)) $map_projection = $_p[1]; else $map_projection = 'epsg:3857';

  $wms_enabled = (strlen($map->getMetaData('wms_enable_request')) > 0);
  if ($wms_enabled) {
    $map_wmstitle = (strlen($map->getMetaData('wms_title')) > 0 ? $map->getMetaData('wms_title') : NULL);
    $map_wmsabstract = (strlen($map->getMetaData('wms_abstract')) > 0 ? $map->getMetaData('wms_abstract') : NULL);
  }

  $layers = $map->getAllLayerNames();
  $layers_json = array();
  foreach ($layers as $k => $name) {
    $layer = $map->getLayer($k);

    $data = array();

    $data['name'] = $layer->name;
    $data['type'] = $layer->type;

    if ($wms_enabled) {
      $data['wms_title'] = (strlen($layer->getMetaData('wms_title')) > 0 ? $layer->getMetaData('wms_title') : NULL);
      $data['wms_abstract'] = (strlen($layer->getMetaData('wms_abstract')) > 0 ? $layer->getMetaData('wms_abstract') : NULL);
      $data['wms_include_items'] = (strlen($layer->getMetaData('wms_include_items')) > 0 ? $layer->getMetaData('wms_include_items') : NULL);
      $data['wms_exclude_items'] = (strlen($layer->getMetaData('wms_exclude_items')) > 0 ? $layer->getMetaData('wms_exclude_items') : NULL);
      $data['wms_attribution_title'] = (strlen($layer->getMetaData('wms_attribution_title')) > 0 ? $layer->getMetaData('wms_attribution_title') : NULL);
      $data['wms_attribution_onlineresource'] = (strlen($layer->getMetaData('wms_attribution_onlineresource')) > 0 ? $layer->getMetaData('wms_attribution_onlineresource') : NULL);
      $data['wms_enable_request'] = (strlen($layer->getMetaData('wms_enable_request')) > 0 ? $layer->getMetaData('wms_enable_request') : NULL);
    }

    if (preg_match('/(epsg:[0-9]+)/i', $layer->getProjection(), $_p)) $data['projection'] = $_p[1]; else $data['projection'] = 'epsg:3857';
    $data['connectiontype'] = $layer->connectiontype;
    $data['connection'] = $layer->connection;
    $data['data'] = $layer->data;
    $data['filteritem'] = $layer->filteritem;
    $data['filter'] = $layer->getFilterString();
    $data['group'] = $layer->group;
    $data['minscaledenom'] = ($layer->minscaledenom != -1 ? $layer->minscaledenom : NULL);
    $data['maxscaledenom'] = ($layer->maxscaledenom != -1 ? $layer->maxscaledenom : NULL);
    $data['opacity'] = $layer->opacity;
    $data['labelitem'] = $layer->labelitem;
    $data['classitem'] = $layer->classitem;

    $data['class'] = array();
    for ($c = 0; $c < $layer->numclasses; $c++) {
      $class = $layer->getClass($c);
      $data['class'][$c]['name'] = $class->name;
      $data['class'][$c]['expression'] = $class->getExpressionString();
      $data['class'][$c]['styles'] = array();
      if ($class->numstyles > 0) {
        for ($s = 0; $s < $class->numstyles; $s++) {
          $style = $class->getStyle($s);
          $data['class'][$c]['style'][$s]['color'] = array('r' => $style->color->red, 'g' => $style->color->green, 'b' => $style->color->blue);
          $data['class'][$c]['style'][$s]['outlinecolor'] = array('r' => $style->outlinecolor->red, 'g' => $style->outlinecolor->green, 'b' => $style->outlinecolor->blue);
          $data['class'][$c]['style'][$s]['width'] = $style->width;
          $data['class'][$c]['style'][$s]['symbolname'] = $style->symbolname;
          $data['class'][$c]['style'][$s]['size'] = $style->size;
        }
      }
      $data['class'][$c]['label'] = array();
      if ($class->numlabels > 0) {
        //for ($l = 0; $l < $class->numlabels; $l++) {
          $label = $class->getLabel(0);

          $data['class'][$c]['label']['align'] = $label->align;
          $data['class'][$c]['label']['position'] = $label->position;
          $data['class'][$c]['label']['color'] = array('r' => $label->color->red, 'g' => $label->color->green, 'b' => $label->color->blue);
          $data['class'][$c]['label']['outlinecolor'] = array('r' => $label->outlinecolor->red, 'g' => $label->outlinecolor->green, 'b' => $label->outlinecolor->blue);
          $data['class'][$c]['label']['minscaledenom'] = ($label->minscaledenom != -1 ? $label->minscaledenom : NULL);
          $data['class'][$c]['label']['maxscaledenom'] = ($label->maxscaledenom != -1 ? $label->maxscaledenom : NULL);
        //}
      }
    }

    $layers_json[$k] = json_encode($data);
  }

  $map->free(); unset($map);
} else {
  $map = new Map($_SESSION['mapfile-generator']['mapfile']);

  $map_name = $map->name;
  $map_extent = $map->extent;
  $map_projection = (!is_null($map->projection) ? $map->projection : 'epsg:3857');

  $wms_enabled = ($map->getMetadata('wms_enable_request') !== FALSE);
  if ($wms_enabled) {
    $map_wmstitle = ($map->getMetadata('wms_title') !== FALSE ? $map->getMetadata('wms_title') : NULL);
    $map_wmsabstract = ($map->getMetadata('wms_abstract') !== FALSE ? $map->getMetadata('wms_abstract') : NULL);
  }

  $layers_json = array();
  foreach ($map->getLayers() as $k => $layer) {
    $data = array();

    if ($wms_enabled) {
      $data['wms_title'] = ($layer->getMetadata('wms_title') !== FALSE ? $layer->getMetadata('wms_title') : NULL);
      $data['wms_abstract'] = ($layer->getMetadata('wms_abstract') !== FALSE ? $layer->getMetadata('wms_abstract') : NULL);
      $data['wms_include_items'] = ($layer->getMetadata('wms_include_items') !== FALSE ? $layer->getMetadata('wms_include_items') : NULL);
      $data['wms_exclude_items'] = ($layer->getMetadata('wms_exclude_items') !== FALSE ? $layer->getMetadata('wms_exclude_items') : NULL);
      $data['wms_attribution_title'] = ($layer->getMetadata('wms_attribution_title') !== FALSE ? $layer->getMetadata('wms_attribution_title') : NULL);
      $data['wms_attribution_onlineresource'] = ($layer->getMetadata('wms_attribution_onlineresource') !== FALSE ? $layer->getMetadata('wms_attribution_onlineresource') : NULL);
      $data['wms_enable_request'] = ($layer->getMetadata('wms_enable_request') !== FALSE ? $layer->getMetadata('wms_enable_request') : NULL);
    }

    $data['name'] = $layer->name;
    $data['type'] = $layer->type;
    $data['connectiontype'] = $layer->connectiontype;
    $data['connection'] = $layer->connection;
    $data['data'] = $layer->data;
    $data['filteritem'] = $layer->filteritem;
    $data['filter'] = $layer->filter;
    $data['group'] = $layer->group;
    $data['minscaledenom'] = $layer->minscaledenom;
    $data['maxscaledenom'] = $layer->maxscaledenom;
    $data['opacity'] = $layer->opacity;
    $data['labelitem'] = $layer->labelitem;
    $data['classitem'] = $layer->classitem;

    $data['class'] = array(); $_classes = $layer->getClasses();
    foreach ($_classes as $c => $class) {
      $data['class'][$c]['name'] = $class->name;
      $data['class'][$c]['expression'] = $class->expression;
      $data['class'][$c]['styles'] = array(); $_styles = $class->getStyles();
      foreach ($_styles as $s => $style) {
        $data['class'][$c]['style'][$s]['color'] = $style->getColor();
        $data['class'][$c]['style'][$s]['outlinecolor'] = $style->getOutlineColor();
        $data['class'][$c]['style'][$s]['width'] = $style->width;
        $data['class'][$c]['style'][$s]['symbolname'] = $style->symbolname;
        $data['class'][$c]['style'][$s]['size'] = $style->size;
      }
      $data['class'][$c]['label'] = array(); //$_labels = $class->getLabels();
      //foreach ($_labels as $l => $label) {
        $label = $class->getLabel(0);

        if ($label) {
        $data['class'][$c]['label']['align'] = $label->align;
        $data['class'][$c]['label']['position'] = $label->position;
        $data['class'][$c]['label']['color'] = $label->getColor();
        $data['class'][$c]['label']['outlinecolor'] = $label->getOutlineColor();
        $data['class'][$c]['label']['minscaledenom'] = ($label->minscaledenom != -1 ? $label->minscaledenom : NULL);
        $data['class'][$c]['label']['maxscaledenom'] = ($label->maxscaledenom != -1 ? $label->maxscaledenom : NULL);
        }
      //}
    }

    $layers_json[$k] = json_encode($data);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MapServer MapFile Generator</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet">
  </head>
  <body>
    <div class="container">
      <h1>MapServer MapFile Generator</h1>
      <?php
      if ($mapscript) echo '<p class="text-success"><i class="fa fa-check"></i> <a href="http://mapserver.org/mapscript/php/index.html" class="text-success">MapScript</a> support enabled (v'.ms_GetVersionInt().').</p>';
      else echo '<p class="text-warning"><i class="fa fa-exclamation-triangle"></i> <a href="http://mapserver.org/mapscript/php/index.html" class="text-warning">MapScript</a> support disabled. Use of internal library.</p>';
      ?>
      <hr>
      <?php if (isset($error)) echo '<div class="alert alert-danger" role="alert"><strong>Error :</strong> '.htmlentities($error).'</div>'; ?>
      <div role="tabpanel">

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" class="active"><a href="#editor" role="tab" data-toggle="tab"><i class="fa fa-pencil"></i> Editor</a></li>
          <li role="presentation"><a href="#mapfile" role="tab" data-toggle="tab"><i class="fa fa-file-text-o"></i> MapFile</a></li>
          <li role="presentation"><a href="#map" role="tab" data-toggle="tab"><i class="fa fa-globe"></i> Map</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">

          <div role="tabpanel" class="tab-pane active" id="editor">
            <form autocomplete="off">
              <div class="row">
                <div class="form-group form-group-lg col-sm-6">
                  <label for="inputName">Map name</label>
                  <input type="text" class="form-control" id="inputName" name="name" value="<?= $map_name ?>" required="required">
                </div>
                <div class="form-group form-group-lg col-sm-6">
                  <label for="selectProj">Map projection</label>
                  <select class="form-control" id="selectProj" name="projection">
                    <option value="epsg:3857"<?= ($map_projection == 'epsg:3857' ? ' selected="selected"' : '') ?>>EPSG:3857 - Spherical Mercator</option>
                    <option value="epsg:4326" data-minx="-180.0" data-miny="-90.0" data-maxx="180.0" data-maxy="90.0"<?= ($map_projection == 'epsg:4326' ? ' selected="selected"' : '') ?>>EPSG:4326 - WGS 84</option>
                    <option value="epsg:31370" data-minx="0" data-miny="0" data-maxx="300000" data-maxy="300000"<?= ($map_projection == 'epsg:31370' ? ' selected="selected"' : '') ?>>EPSG:31370 - Belge 1972 / Belgian Lambert 72</option>
                    <option value="epsg:900913"<?= ($map_projection == 'epsg:900913' ? ' selected="selected"' : '') ?>>EPSG:900913 - Spherical Mercator</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group form-group-sm col-sm-3">
                  <label for="inputExtentMinX">Map extent : MIN X</label>
                  <input type="text" class="form-control" id="inputExtentMinX" name="extentminx" value="<?= $map_extent[0] ?>" required="required">
                </div>
                <div class="form-group form-group-sm col-sm-3">
                  <label for="inputExtentMinY">Map extent : MIN Y</label>
                  <input type="text" class="form-control" id="inputExtentMinY" name="extentminy" value="<?= $map_extent[1] ?>" required="required">
                </div>
                <div class="form-group form-group-sm col-sm-3">
                  <label for="inputExtentMaxX">Map extent : MAX X</label>
                  <input type="text" class="form-control" id="inputExtentMaxX" name="extentmaxx" value="<?= $map_extent[2] ?>" required="required">
                </div>
                <div class="form-group form-group-sm col-sm-3">
                  <label for="inputExtentMaxY">Map extent : MAX Y</label>
                  <input type="text" class="form-control" id="inputExtentMaxY" name="extentmaxy" value="<?= $map_extent[3] ?>" required="required">
                </div>
              </div>
              <div>
                <div class="checkbox"><label><input type="checkbox" name="wms" value="1"<?= ($wms_enabled ? ' checked="checked"' : '') ?>> Enable WMS</label></div>
                <div class="form-horizontal wms-control"<?= (!$wms_enabled ? ' style="display:none;"' : '') ?>>
                  <div class="form-group">
                    <label for="inputWMSTitle" class="col-sm-3 control-label">WMS Title</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="inputWMSTitle" name="wms_title" value="<?= (isset($map_wmstitle) ? $map_wmstitle : '') ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputWMSAbstract" class="col-sm-3 control-label">WMS Abstract</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="inputWMSAbstract" name="wms_abstract" value="<?= (isset($map_wmsabstract) ? $map_wmsabstract : '') ?>">
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
                      <th class="col-sm-8">Name</th>
                      <th class="col-sm-3">Type</th>
                      <th class="col-sm-1"></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  foreach ($layers_json as $k => $json) {
                    $data = json_decode($json, TRUE);
                  ?>
                    <tr class="layer" id="layer<?= $k ?>">
                      <td>
                        <input type="text" class="form-control" id="inputLayer<?= $k ?>Name" name="layers[<?= $k ?>][name]" value="<?= $data['name'] ?>" required="required">
                      </td>
                      <td>
                        <select class="form-control" id="selectLayer<?= $k ?>Type" name="layers[<?= $k ?>][type]">
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
                            <li class="wms-control"<?= (!$wms_enabled ? ' style="display:none;"' : '') ?>><a href="#modal-wms" data-toggle="modal"><i class="fa fa-globe"></i> WMS</a></li>
                            <li role="presentation" class="divider"></li>
                            <li><a href="#delete"><i class="fa fa-trash-o"></i> Delete</a></li>
                            <li><a href="#duplicate"><i class="fa fa-files-o"></i> Duplicate</a></li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php
                  }

                  $count = count($layers_json);
                  ?>
                    <tr class="layer" id="layer<?= $count ?>">
                      <td>
                        <input type="text" class="form-control" id="inputLayer<?= $count ?>Name" name="layers[<?= $count ?>][name]" required="required">
                      </td>
                      <td>
                        <select class="form-control" id="selectLayer<?= $count ?>Type" name="layers[<?= $count ?>][type]">
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
                            <li class="wms-control"<?= (!$wms_enabled ? ' style="display:none;"' : '') ?>><a href="#modal-wms" data-toggle="modal"><i class="fa fa-globe"></i> WMS</a></li>
                            <li role="presentation" class="divider"></li>
                            <li><a href="#delete"><i class="fa fa-trash-o"></i> Delete</a></li>
                            <li><a href="#duplicate"><i class="fa fa-files-o"></i> Duplicate</a></li>
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
      <?php echo '$(document).ready(function(){'; foreach($layers_json as $k => $json) { echo "$('#layer".$k."').data(".$json.");"; } echo '});'.PHP_EOL; ?>
    </script>
    <script src="js/main.js"></script>
    <script src="js/modal-data.js"></script>
    <script src="js/modal-class.js"></script>
    <script src="js/modal-label.js"></script>
    <script src="js/modal-style.js"></script>
    <script src="js/modal-wms.js"></script>
  </body>
</html>