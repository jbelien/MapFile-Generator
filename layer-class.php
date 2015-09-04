<?php
session_start();

$settings = parse_ini_file('settings.ini');
$mapscript = extension_loaded('mapscript');

$tmp = sys_get_temp_dir();
if (!file_exists($tmp.'/mapserver') || !is_dir($tmp.'/mapserver')) mkdir($tmp.'/mapserver');

if (isset($settings['library']) && file_exists($settings['library']) && is_dir($settings['library'])) {
  require($settings['library'].'/map.php');
  require($settings['library'].'/legend.php');
  require($settings['library'].'/scalebar.php');
  require($settings['library'].'/layer.php');
  require($settings['library'].'/class.php');
  require($settings['library'].'/style.php');
  require($settings['library'].'/label.php');
}

if (!$mapscript && !class_exists('MapFile\Map')) $error = 'This application needs <a href="http://www.mapserver.org/mapscript/php/" target="_blank">MapScript</a> or <a href="https://github.com/jbelien/MapFile-PHP-Library" target="_blank">MapFile-PHP-Library</a> ! Enable MapScript or download and link MapFile-PHP-Library (see <a href="https://github.com/jbelien/MapFile-Generator#libraries" target="_blank">documentation</a>).';

require_once('fn.php');

$source = NULL; $mapfile = NULL;
if (isset($_SESSION['mapfile-generator']['source']) && file_exists($_SESSION['mapfile-generator']['source'])) $source = $_SESSION['mapfile-generator']['source'];
if (isset($_SESSION['mapfile-generator']['mapfile']) && file_exists($_SESSION['mapfile-generator']['mapfile'])) $mapfile = $_SESSION['mapfile-generator']['mapfile'];

if (/*is_null($source) || */is_null($mapfile) || !isset($_GET['layer'])) { header('Location:index.php'); exit(); }

if ($mapscript && isset($_POST['action']) && $_POST['action'] == 'save') {
  try {
    $map = new mapObj($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    $l->minscaledenom = (!empty($_POST['minscaledenom']) ? floatval($_POST['minscaledenom']) : -1);
    $l->maxscaledenom = (!empty($_POST['maxscaledenom']) ? floatval($_POST['maxscaledenom']) : -1);
    $l->opacity = intval($_POST['opacity']);
    $l->labelitem = $_POST['labelitem'];
    $l->classitem = $_POST['classitem'];

    $l->free(); unset($l);

    $map->save($mapfile);
    $map->free(); unset($map);

    header('Location: index.php');
    exit();
  } catch (MapScriptException $e) {
    $error = $e->getMessage();
  }
}
else if (isset($_POST['action']) && $_POST['action'] == 'save') {
  try {
    $map = new MapFile\Map($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    $l->minscaledenom = (!empty($_POST['minscaledenom']) ? floatval($_POST['minscaledenom']) : NULL);
    $l->maxscaledenom = (!empty($_POST['maxscaledenom']) ? floatval($_POST['maxscaledenom']) : NULL);
    $l->opacity = intval($_POST['opacity']);
    $l->labelitem = $_POST['labelitem'];
    $l->classitem = $_POST['classitem'];

    $map->save($mapfile);

    header('Location: index.php');
    exit();
  } catch (MapFile\Exception $e) {
    $error = $e->getMessage();
  }
}
else if ($mapscript && isset($_POST['action']) && $_POST['action'] == 'save-class') {
  try {
    $map = new mapObj($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    if (isset($_POST['class']))
      $c = $l->getClass(intval($_POST['class']));
    else
      $c = new classObj($l);

    $c->name = $_POST['name'];
    $c->setExpression($_POST['expression']);

    $c->free(); unset($c);
    $l->free(); unset($l);

    $map->save($mapfile);
    $map->free(); unset($map);

    header('Location: layer-class.php?layer='.$_GET['layer']);
    exit();
  } catch (MapScriptException $e) {
    $error = $e->getMessage();
  }
}
else if (isset($_POST['action']) && $_POST['action'] == 'save-class') {
  try {
    $map = new MapFile\Map($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    if (isset($_POST['class']))
      $c = $l->getClass(intval($_POST['class']));
    else {
      $c = new MapFile\LayerClass();
      $l->addClass($c);
    }

    $c->name = $_POST['name'];
    $c->expression = $_POST['expression'];

    $map->save($mapfile);

    header('Location: layer-class.php?layer='.$_GET['layer']);
    exit();
  } catch (MapFile\Exception $e) {
    $error = $e->getMessage();
  }
}
else if ($mapscript && (isset($_GET['down']) || isset($_GET['up']) || isset($_GET['remove']))) {
  try {
    $map = new mapObj($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    if (isset($_GET['down'])) $l->moveclassdown(intval($_GET['down']));
    else if (isset($_GET['up'])) $l->moveclassup(intval($_GET['up']));
    else if (isset($_GET['remove'])) $l->removeClass(intval($_GET['remove']));

    $l->free(); unset($l);

    $map->save($mapfile);
    $map->free(); unset($map);

    header('Location: layer-class.php?layer='.$_GET['layer']);
    exit();
  } catch (MapScriptException $e) {
    $error = $e->getMessage();
  }
}
else if (isset($_GET['down']) || isset($_GET['up']) || isset($_GET['remove'])) {
  try {
    $map = new MapFile\Map($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    if (isset($_GET['down'])) $l->moveClassDown(intval($_GET['down']));
    else if (isset($_GET['up'])) $l->moveClassUp(intval($_GET['up']));
    else if (isset($_GET['remove'])) $l->removeClass(intval($_GET['remove']));

    $map->save($mapfile);

    header('Location: layer-class.php?layer='.$_GET['layer']);
    exit();
  } catch (MapFile\Exception $e) {
    $error = $e->getMessage();
  }
}

$meta = mapfile_getmeta($mapfile);
$layers = mapfile_getlayers($mapfile);
$layer = $layers[intval($_GET['layer'])];
$class_json = json_encode($layer['class']);

page_header('Layer: '.$layer['name']);
?>
<div class="container">
  <h1>Map: <a href="index.php"><?= htmlentities($meta['name']) ?></a></h1>
  <h2>Layer: <?= htmlentities($layer['name']) ?></h2>

  <?php if (isset($error)) echo '<div class="alert alert-danger" role="alert"><strong>Error :</strong> '.$error.'</div>'; ?>

  <form class="form-horizontal" action="layer-class.php?layer=<?= $_GET['layer'] ?>" method="post">
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
      <button type="submit" class="btn btn-primary" name="action" value="save"><i class="fa fa-floppy-o"></i> Save</button>
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
            <th colspan="4"></th>
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
            echo '<td class="text-center" style="width:20px;"><a href="#modal-class" data-toggle="modal" title="Edit"><i class="fa fa-pencil-square-o"></i></a></td>';
            echo '<td class="text-center" style="width:20px;">'.($i < (count($layer['class'])-1) ? '<a href="?layer='.$_GET['layer'].'&amp;down='.$i.'" title="Move down"><i class="fa fa-arrow-down"></i></a>' : '').'</td>';
            echo '<td class="text-center" style="width:20px;">'.($i > 0 ? '<a href="?layer='.$_GET['layer'].'&amp;up='.$i.'" title="Move up"><i class="fa fa-arrow-up"></i></a>' : '').'</td>';
            echo '<td class="text-center" style="width:20px;"><a href="?layer='.$_GET['layer'].'&amp;remove='.$i.'" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
            echo '<td style="border-left: 1px solid #DDD;"><a href="layer-style-label.php?layer='.intval($_GET['layer']).'&amp;class='.$i.'&amp;style" style="text-decoration:none;"><i class="fa fa-paint-brush"></i> '.count($c['style']).' style'.(count($c['style']) > 1 ? 's' : '').'</a></td>';
            echo '<td><a href="layer-style-label.php?layer='.intval($_GET['layer']).'&amp;class='.$i.'&amp;label" style="text-decoration:none;"><i class="fa fa-tag"></i> '.count($c['label']).' label'.(count($c['label']) > 1 ? 's' : '').'</a></td>';
          echo '</tr>'.PHP_EOL;
        }
?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="9" class="text-right"><a href="#modal-class" data-toggle="modal" style="text-decoration:none;"><i class="fa fa-plus-square"></i> Add new class</a></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="col-sm-4">
      <img style="margin:auto;" src="<?= $settings['mapserv'] ?>?map=<?= $mapfile ?>&amp;mode=legend&amp;layers=<?= $layer['name'] ?>&amp;<?= time() ?>" alt="Legend &quot;<?= htmlentities($layer['name']) ?>&quot;" class="thumbnail">
    </div>
  </div>

</div>

<div id="modal-class" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <form action="layer-class.php?layer=<?= $_GET['layer'] ?>" method="post" class="form-horizontal" autocomplete="off">
      <input type="hidden" name="class">
      <div class="modal-body">
        <div class="form-group">
          <label for="inputName" class="col-sm-3 control-label">Name</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="inputName" name="name">
          </div>
        </div>
        <div class="form-group">
          <label for="inputExpression" class="col-sm-3 control-label">Expression</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="inputExpression" name="expression">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="action" value="save-class">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
  var c = <?= $class_json ?>;

  $(document).ready(function() {
    $('input[type=range]').on('change input', function() { $(this).parent('.input-group').find('.input-group-addon').text($(this).val()); });

    $('a.text-danger').on('click', function(event) { if (!confirm('Are you sure you want to delete this class ?')) { event.preventDefault(); } });

    $('#modal-class').on('show.bs.modal', function(event) {
      $(this).find('form')[0].reset();

      if ($(event.relatedTarget).has('.fa-plus-square').length > 0) {
        $(this).find('h4').html('<i class="fa fa-plus-square"></i> New class');
        $(this).find('input[name=class]').prop('disabled', true);
      } else {
        var i = $(event.relatedTarget).closest('tr').index();
        var _class = c[i];
        $(this).find('h4').html('<i class="fa fa-pencil-square-o"></i> '+_class.name);
        $(this).find('input[name=class]').prop('disabled', false).val(i);
        $(this).find('#inputName').val(_class.name);
        $(this).find('#inputExpression').val(_class.expression);
      }
    });
  });
</script>
<?php
page_footer();