<?php
/*
 *
 */
function page_header($title = '') {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MapFile Generator<?= (!empty($title) ? ' - '.htmlentities($title) : '') ?></title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.11.2.min.js"><\/script>')</script>
  </head>
  <body>
    <nav class="navbar navbar-default">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <span class="navbar-brand">MapFile Generator</span>
        </div>

        <div class="collapse navbar-collapse" id="navbar-collapse">
          <ul class="nav navbar-nav">
            <li<?= (basename($_SERVER['PHP_SELF']) == 'index.php' || substr(basename($_SERVER['PHP_SELF']), 0, 5) == 'layer' ? ' class="active"' : '') ?>><a href="index.php"><i class="fa fa-pencil"></i> Editor</a></li>
            <li<?= (basename($_SERVER['PHP_SELF']) == 'mapfile.php' ? ' class="active"' : '') ?>><a href="mapfile.php"><i class="fa fa-file-text-o"></i> MapFile</a></li>
            <li<?= (basename($_SERVER['PHP_SELF']) == 'map.php' ? ' class="active"' : '') ?>><a href="map.php"><i class="fa fa-globe"></i> Map</a></li>
          </ul>
<?php
          if (extension_loaded('mapscript')) echo '<p class="navbar-text navbar-right text-success" style="color:#3C763D;"><i class="fa fa-check"></i> <a href="http://mapserver.org/mapscript/php/index.html" class="text-success">MapScript</a> support enabled (v'.ms_GetVersionInt().').</p>';
          else echo '<p class="navbar-text navbar-right text-warning"><i class="fa fa-exclamation-triangle"></i> <a href="http://mapserver.org/mapscript/php/index.html" class="text-warning">MapScript</a> support disabled. Use of internal library.</p>';
?>
        </div>
      </div>
    </nav>
<?php
}

/*
 *
 */
function page_footer($scripts = NULL) {
?>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<?php if (!is_null($scripts) && is_array($scripts)) foreach($scripts as $s) echo '    <script src="'.$s.'"></script>'.PHP_EOL; ?>
  </body>
</html>
<?php
}


/*
 *
 */
function mapfile_getmeta($fname) {
  if (extension_loaded('mapscript')) {
    $map = new mapObj($_SESSION['mapfile-generator']['mapfile']);

    $meta = array();

    $meta['name'] = $map->name;
    $meta['extent'] = array($map->extent->minx, $map->extent->miny, $map->extent->maxx, $map->extent->maxy);
    if (preg_match('/(epsg:[0-9]+)/i', $map->getProjection(), $_p)) $meta['projection'] = $_p[1]; else $map_projection = 'epsg:3857';

    $meta['wms'] = (strlen($map->getMetaData('wms_enable_request')) > 0);
    if ($meta['wms']) {
      $meta['wmstitle'] = (strlen($map->getMetaData('wms_title')) > 0 ? $map->getMetaData('wms_title') : NULL);
      $meta['wmsabstract'] = (strlen($map->getMetaData('wms_abstract')) > 0 ? $map->getMetaData('wms_abstract') : NULL);
    }

    $map->free(); unset($map);
  } else {
    $map = new Map($_SESSION['mapfile-generator']['mapfile']);

    $meta = array();

    $meta['name'] = $map->name;
    $meta['extent'] = $map->extent;
    $meta['projection'] = (!is_null($map->projection) ? $map->projection : 'epsg:3857');

    $meta['wms'] = ($map->getMetadata('wms_enable_request') !== FALSE);
    if ($meta['wms']) {
      $meta['wmstitle'] = ($map->getMetadata('wms_title') !== FALSE ? $map->getMetadata('wms_title') : NULL);
      $meta['wmsabstract'] = ($map->getMetadata('wms_abstract') !== FALSE ? $map->getMetadata('wms_abstract') : NULL);
    }
  }

  if (isset($meta)) return $meta; else return FALSE;
}

/*
 *
 */
function mapfile_getlayers($fname) {
  if (extension_loaded('mapscript')) {
    $map = new mapObj($fname);

    $map_name = $map->name;
    $map_extent = array($map->extent->minx, $map->extent->miny, $map->extent->maxx, $map->extent->maxy);
    if (preg_match('/(epsg:[0-9]+)/i', $map->getProjection(), $_p)) $map_projection = $_p[1]; else $map_projection = 'epsg:3857';

    $wms_enabled = (strlen($map->getMetaData('wms_enable_request')) > 0);
    if ($wms_enabled) {
      $map_wmstitle = (strlen($map->getMetaData('wms_title')) > 0 ? $map->getMetaData('wms_title') : NULL);
      $map_wmsabstract = (strlen($map->getMetaData('wms_abstract')) > 0 ? $map->getMetaData('wms_abstract') : NULL);
    }

    $layers = $map->getAllLayerNames(); $_layers = array();
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
      $data['status'] = $layer->status;

      $data['class'] = array();
      for ($c = 0; $c < $layer->numclasses; $c++) {
        $class = $layer->getClass($c);
        $data['class'][$c]['name'] = $class->name;
        $data['class'][$c]['expression'] = $class->getExpressionString();
        $data['class'][$c]['style'] = array();
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
          for ($l = 0; $l < $class->numlabels; $l++) {
            $label = $class->getLabel($l);
            $data['class'][$c]['label'][$l]['size'] = $label->size;
            $data['class'][$c]['label'][$l]['align'] = $label->align;
            $data['class'][$c]['label'][$l]['position'] = $label->position;
            $data['class'][$c]['label'][$l]['color'] = array('r' => $label->color->red, 'g' => $label->color->green, 'b' => $label->color->blue);
            $data['class'][$c]['label'][$l]['outlinecolor'] = array('r' => $label->outlinecolor->red, 'g' => $label->outlinecolor->green, 'b' => $label->outlinecolor->blue);
            $data['class'][$c]['label'][$l]['minscaledenom'] = ($label->minscaledenom != -1 ? $label->minscaledenom : NULL);
            $data['class'][$c]['label'][$l]['maxscaledenom'] = ($label->maxscaledenom != -1 ? $label->maxscaledenom : NULL);
          }
        }
      }

      $_layers[$k] = $data;
    }

    $map->free(); unset($map);
  } else {
    $map = new Map($fname);

    $map_name = $map->name;
    $map_extent = $map->extent;
    $map_projection = (!is_null($map->projection) ? $map->projection : 'epsg:3857');

    $wms_enabled = ($map->getMetadata('wms_enable_request') !== FALSE);
    if ($wms_enabled) {
      $map_wmstitle = ($map->getMetadata('wms_title') !== FALSE ? $map->getMetadata('wms_title') : NULL);
      $map_wmsabstract = ($map->getMetadata('wms_abstract') !== FALSE ? $map->getMetadata('wms_abstract') : NULL);
    }

    $layers = $map->getLayers(); $_layers = array();
    foreach ($layers as $k => $layer) {
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
        foreach ($_labels as $l => $label) {
          $label = $class->getLabel(0);

          if ($label) {
          $data['class'][$c]['label'][$l]['size'] = $label->size;
          $data['class'][$c]['label'][$l]['align'] = $label->align;
          $data['class'][$c]['label'][$l]['position'] = $label->position;
          $data['class'][$c]['label'][$l]['color'] = $label->getColor();
          $data['class'][$c]['label'][$l]['outlinecolor'] = $label->getOutlineColor();
          $data['class'][$c]['label'][$l]['minscaledenom'] = ($label->minscaledenom != -1 ? $label->minscaledenom : NULL);
          $data['class'][$c]['label'][$l]['maxscaledenom'] = ($label->maxscaledenom != -1 ? $label->maxscaledenom : NULL);
          }
        }
      }

      $_layers[$k] = $data;
    }
  }

  if (isset($_layers)) return $_layers; else return FALSE;
}