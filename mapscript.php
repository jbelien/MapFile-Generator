<?php
session_start();

$settings = parse_ini_file('settings.ini');

$mapfile = $_SESSION['mapfile-generator']['mapfile'];

$map = new mapObj($mapfile);

if (isset($_POST['up']) || isset($_POST['down']) || isset($_POST['delete'])) {
  if (isset($_POST['up'])) $map->moveLayerUp(intval($_POST['up']));
  else if (isset($_POST['down'])) $map->moveLayerDown(intval($_POST['down']));
  else if (isset($_POST['delete'])) $map->removeLayer(intval($_POST['delete']));
}
else {
  $map->setProjection($_POST['projection'], MS_TRUE);

  if (strlen($_POST['extentminx']) > 0 && strlen($_POST['extentminy']) > 0 && strlen($_POST['extentmaxx']) > 0 && strlen($_POST['extentmaxy']) > 0) {
    $map->setExtent($_POST['extentminx'], $_POST['extentminy'], $_POST['extentmaxx'], $_POST['extentmaxy']);
  }

  $map->setSize(500, 500);
  $map->setFontSet($settings['fontset']);
  $map->setSymbolSet($settings['symbolset']);

  if (!empty($_POST['name'])) $map->name = trim($_POST['name']);

  if (isset($_POST['wms']) && $_POST['wms'] == 1) {
    $map->setMetaData('wms_enable_request', '*');
    $map->setMetaData('wms_feature_info_mime_type', 'text/plain application/vnd.ogc.gml');
    $map->setMetaData('wms_srs', 'EPSG:31370 EPSG:4326 EPSG:3857');

    if (!empty($_POST['wms_title'])) $map->setMetaData('wms_title', $_POST['wms_title']);
    if (!empty($_POST['wms_abstract'])) $map->setMetaData('wms_abstract', $_POST['wms_abstract']);
    if (!empty($_POST['wms_attribution_title'])) $map->setMetaData('wms_attribution_title', $_POST['wms_attribution_title']);
    if (!empty($_POST['wms_attribution_onlineresource'])) $map->setMetaData('wms_attribution_onlineresource', $_POST['wms_attribution_onlineresource']);

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

  $map->legend->label->type = MS_TRUETYPE;
  $map->legend->label->font = $settings['font'];
  $map->legend->label->size = 8.0;

  $map->scalebar->label->type = MS_TRUETYPE;
  $map->scalebar->label->font = $settings['font'];
  $map->scalebar->label->size = 8.0;
  $map->scalebar->units = MS_KILOMETERS;
  $map->scalebar->color->setRGB(0,0,0);
  $map->scalebar->outlinecolor->setRGB(0,0,0);

  if (isset($_POST['layers']) && is_array($_POST['layers'])) {
    foreach ($_POST['layers'] as $i => $_layer) {
      if (!isset($_layer['name']) || empty($_layer['name'])) continue;

      try { $layer = $map->getLayer($i); } catch (MapScriptException $e) { $layer = new layerObj($map); }

      if (isset($_layer['type'])) $layer->type = intval($_layer['type']);
      if (isset($_layer['name'])) $layer->name = trim($_layer['name']);

      if (isset($_POST['wms']) && $_POST['wms'] == 1) {
        /*if (empty($_layer['request'])) {
          $layer->setMetadata('wms_enable_request', 'none');
        } else {
          $r = '*';
          if (!in_array('GetCapabilities', $_layer['request'])) $r.= ' !GetCapabilities';
          if (!in_array('GetMap', $_layer['request'])) $r.= ' !GetMap';
          if (!in_array('GetFeatureInfo', $_layer['request'])) $r.= ' !GetFeatureInfo';
          if (!in_array('GetLegendGraphic', $_layer['request'])) $r.= ' !GetLegendGraphic';
          $layer->setMetadata('wms_enable_request', $r);
        }*/

        if (isset($_layer['wms_title'])) { if (!empty($_layer['wms_title'])) $layer->setMetaData('wms_title', trim($_layer['wms_title'])); else if (strlen($layer->getMetaData('wms_title')) > 0) $layer->removeMetaData('wms_title'); }
        if (isset($_layer['wms_abstract'])) { if (!empty($_layer['wms_abstract'])) $layer->setMetaData('wms_abstract', trim($_layer['wms_abstract'])); else if (strlen($layer->getMetaData('wms_abstract')) > 0) $layer->removeMetaData('wms_abstract'); }
        if (isset($_layer['wms_attribution_title'])) { if (!empty($_layer['wms_attribution_title'])) $layer->setMetaData('wms_attribution_title', trim($_layer['wms_attribution_title'])); else if (strlen($layer->getMetaData('wms_attribution_title')) > 0) $layer->removeMetaData('wms_attribution_title'); }
        if (isset($_layer['wms_attribution_onlineresource'])) { if (!empty($_layer['wms_attribution_onlineresource'])) $layer->setMetaData('wms_attribution_onlineresource', trim($_layer['wms_attribution_onlineresource'])); else if (strlen($layer->getMetaData('wms_attribution_onlineresource')) > 0) $layer->removeMetaData('wms_attribution_onlineresource'); }

        if (isset($_layer['request']) && in_array('GetFeatureInfo', $_layer['request'])) {
          $layer->template = 'dummy.html';
          $layer->setMetaData('wms_include_items', '*');

          //$layer->setMetaData('gml_geometries', 'msGeometry');
          //$layer->setMetaData('gml_msGeometry_type', '');
          //$layer->setMetaData('wms_exclude_items', '');
        } else {
          $layer->template = NULL;
          if (strlen($map->getMetaData('wms_include_items')) > 0) $layer->removeMetaData('wms_include_items');

          //$layer->removeMetaData('gml_geometries');
          //$layer->removeMetaData('gml_msGeometry_type');
          //$layer->removeMetaData('wms_exclude_items');
        }

      } else {
        if (strlen($map->getMetaData('wms_title')) > 0) $layer->removeMetaData('wms_title');
        if (strlen($map->getMetaData('wms_abstract')) > 0) $layer->removeMetaData('wms_abstract');
        if (strlen($map->getMetaData('wms_attribution_title')) > 0) $layer->removeMetaData('wms_attribution_title');
        if (strlen($map->getMetaData('wms_attribution_onlineresource')) > 0) $layer->removeMetaData('wms_attribution_onlineresource');
      }

      if (isset($_layer['projection'])) $layer->setProjection($_layer['projection']);
      if (isset($_layer['connectiontype'])) $layer->setConnectionType($_layer['connectiontype']);
      if (isset($_layer['connection']) && !empty($_layer['connection'])) $layer->connection = $_layer['connection'];
      if (isset($_layer['data']) && !empty($_layer['data'])) $layer->data = $_layer['data'];
      if (isset($_layer['filter']) && !empty($_layer['filter'])) $layer->setFilter($_layer['filter']);
      if (isset($_layer['group']) && !empty($_layer['group'])) $layer->group = $_layer['group'];
      if (isset($_layer['minscaledenom']) && strlen($_layer['minscaledenom']) > 0) $layer->minscaledenom = floatval($_layer['minscaledenom']);
      if (isset($_layer['maxscaledenom']) && strlen($_layer['maxscaledenom']) > 0) $layer->maxscaledenom = floatval($_layer['maxscaledenom']);
      if (isset($_layer['opacity'])) $layer->opacity = intval($_layer['opacity']);
      if (isset($_layer['labelitem']) && !empty($_layer['labelitem'])) $layer->labelitem = $_layer['labelitem'];
      if (isset($_layer['classitem']) && !empty($_layer['classitem'])) $layer->classitem = $_layer['classitem'];

      if (isset($_layer['class']) && is_array($_layer['class'])) {
        foreach ($_layer['class'] as $c => $_class) {
          try { $class = $layer->getClass($c); } catch (MapScriptException $e) { $class = new classObj($layer); }

          if (isset($_class['name']) && !empty($_class['name'])) $class->name = $_class['name'];
          if (isset($_class['expression']) && !empty($_class['expression'])) $class->setExpression($_class['expression']);

          if (isset($_class['style']) && is_array($_class['style'])) {
            foreach ($_class['style'] as $s => $_style) {
              try { $style = $class->getStyle($s); } catch (MapScriptException $e) { $style = new styleObj($class); }

              $_style['color'] = array_filter($_style['color'], function($v) { return (is_numeric($v) && $v >=0 && $v <= 255); });
              $_style['outlinecolor'] = array_filter($_style['outlinecolor'], function($v) { return (is_numeric($v) && $v >=0 && $v <= 255); });

              if (isset($_style['color']) && !empty($_style['color']) && array_sum($_style['color']) >= 0) $style->color->setRGB($_style['color']['r'], $_style['color']['g'], $_style['color']['b']);
              if (isset($_style['outlinecolor']) && !empty($_style['outlinecolor']) && array_sum($_style['outlinecolor']) >= 0) $style->outlinecolor->setRGB($_style['outlinecolor']['r'], $_style['outlinecolor']['g'], $_style['outlinecolor']['b']);
              if (isset($_style['width']) && floatval($_style['width']) > 0) $style->width = floatval($_style['width']);
              if (isset($_style['symbolname']) && !empty($_style['symbolname'])) $style->symbolname = $_style['symbolname'];
              if (isset($_style['size']) && floatval($_style['size']) > 0) $style->size = floatval($_style['size']);

              $style->free(); unset($style);
            }

            for ($i = 0; $i < $class->numstyles; $i++) { if (!isset($_style[$i]) || empty($_style[$i])) $class->removeStyle($i); }
          }
          if (isset($_class['label'])) {
            $_label = $_class['label'];

            try { $label = $class->getLabel(0); } catch (MapScriptException $e) { $label = new labelObj(); $class->addLabel($label); }

            $_label['color'] = array_filter($_label['color'], function($v) { return (is_numeric($v) && $v >=0 && $v <= 255); });
            $_label['outlinecolor'] = array_filter($_label['outlinecolor'], function($v) { return (is_numeric($v) && $v >=0 && $v <= 255); });

            $label->type = MS_TRUETYPE;
            $label->font = $settings['font'];
            $label->size = 7.0;

            if (isset($_label['align'])) $label->align = $_label['align'];
            if (isset($_label['position'])) $label->position = $_label['position'];
            if (isset($_label['color']) && !empty($_label['color']) && array_sum($_label['color']) >= 0) $label->color->setRGB($_label['color']['r'], $_label['color']['g'], $_label['color']['b']);
            if (isset($_label['outlinecolor']) && !empty($_label['outlinecolor']) && array_sum($_label['outlinecolor']) >= 0) $label->outlinecolor->setRGB($_label['outlinecolor']['r'], $_label['outlinecolor']['g'], $_label['outlinecolor']['b']);
            if (isset($_label['minscaledenom']) && strlen($_label['minscaledenom']) > 0) $label->minscaledenom = floatval($_label['minscaledenom']);
            if (isset($_label['maxscaledenom']) && strlen($_label['maxscaledenom']) > 0) $label->maxscaledenom = floatval($_label['maxscaledenom']);

            $label->free(); unset($label);
          }

          $class->free(); unset($class);
        }
      }

      for ($i = 0; $i < $layer->numclasses; $i++) { if (!isset($_layer[$i]) || empty($_layer[$i])) $layer->removeClass($i); }

      $layer->free(); unset($layer);
    }

    for ($i = 0; $i < $map->numlayers; $i++) { if (!isset($_POST['layers'][$i]) || empty($_POST['layers'][$i])) $map->removeLayer($i); }
  }
}

$map->save($mapfile);
$map->free(); unset($map);

echo file_get_contents($mapfile);

exit();