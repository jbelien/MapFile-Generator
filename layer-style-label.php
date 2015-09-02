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

if (/*is_null($source) || */is_null($mapfile) || !isset($_GET['layer']) || !isset($_GET['class'])) { header('Location:index.php'); exit(); }

if ($mapscript && isset($_POST['action']) && $_POST['action'] == 'save-style') {
  $map = new mapObj($mapfile);

  if (isset($_GET['layer']))
    try { $l = $map->getLayer(intval($_GET['layer'])); } catch (MapScriptException $e) { $error = $e->getMessage(); }
  else
    $l = new layerObj($map);

  $c = $l->getClass(intval($_GET['class']));

  if (isset($_POST['style']))
    $s = $c->getStyle(intval($_POST['style']));
  else
    $s = new styleObj($c);

  if (strlen($_POST['color']['r'])+strlen($_POST['color']['g'])+strlen($_POST['color']['b']) == 0)
    $s->color->setRGB(-1,-1,-1);
  else
    $s->color->setRGB(intval($_POST['color']['r']),intval($_POST['color']['g']),intval($_POST['color']['b']));

  if (strlen($_POST['outlinecolor']['r'])+strlen($_POST['outlinecolor']['g'])+strlen($_POST['outlinecolor']['b']) == 0)
    $s->outlinecolor->setRGB(-1,-1,-1);
  else
    $s->outlinecolor->setRGB(intval($_POST['outlinecolor']['r']),intval($_POST['outlinecolor']['g']),intval($_POST['outlinecolor']['b']));

  $s->width = (!empty($_POST['width']) ? floatval($_POST['width']) : -1);
  if (!empty($_POST['symbolname'])) $s->symbolname = $_POST['symbolname']; else unset($s->symbolname);
  $s->size = (!empty($_POST['size']) ? floatval($_POST['size']) : -1);
  if (!empty($_POST['pattern'])) $s->setPattern(explode(' ', $_POST['pattern'])); else unset($s->pattern);

  $s->free(); unset($s);
  $c->free(); unset($c);
  $l->free(); unset($l);

  $map->save($mapfile);
  $map->free(); unset($map);

  header('Location: layer-style-label.php?layer='.$_GET['layer'].'&class='.$_GET['class'].'&style');
  exit();
}
else if ($mapscript && (isset($_GET['style-down']) || isset($_GET['style-up']) || isset($_GET['style-remove']))) {
  try {
    $map = new mapObj($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    $c = $l->getClass(intval($_GET['class']));

    if (isset($_GET['style-down'])) $c->movestyledown(intval($_GET['style-down']));
    else if (isset($_GET['style-up'])) $c->movestyleup(intval($_GET['style-up']));
    else if (isset($_GET['style-remove'])) $c->deletestyle(intval($_GET['style-remove']));

    $c->free(); unset($c);
    $l->free(); unset($l);

    $map->save($mapfile);
    $map->free(); unset($map);
  } catch (MapScriptException $e) {
    $error = $e->getMessage();
  }

  header('Location: layer-style-label.php?layer='.$_GET['layer'].'&class='.$_GET['class'].'&style');
  exit();
}
else if ($mapscript && isset($_POST['action']) && $_POST['action'] == 'save-label') {
  $map = new mapObj($mapfile);

  if (isset($_GET['layer']))
    try { $l = $map->getLayer(intval($_GET['layer'])); } catch (MapScriptException $e) { $error = $e->getMessage(); }
  else
    $l = new layerObj($map);

  $c = $l->getClass(intval($_GET['class']));

  if (isset($_POST['label']))
    $la = $c->getLabel(intval($_POST['label']));
  else
    $la = new labelObj();

  if (strlen($_POST['color']['r'])+strlen($_POST['color']['g'])+strlen($_POST['color']['b']) == 0)
    $la->color->setRGB(-1,-1,-1);
  else
    $la->color->setRGB(intval($_POST['color']['r']),intval($_POST['color']['g']),intval($_POST['color']['b']));

  if (strlen($_POST['outlinecolor']['r'])+strlen($_POST['outlinecolor']['g'])+strlen($_POST['outlinecolor']['b']) == 0)
    $la->outlinecolor->setRGB(-1,-1,-1);
  else
    $la->outlinecolor->setRGB(intval($_POST['outlinecolor']['r']),intval($_POST['outlinecolor']['g']),intval($_POST['outlinecolor']['b']));

  $la->align = $_POST['align'];
  $la->position = (!empty($_POST['position']) ? $_POST['position'] : -1);
  $la->minscaledenom = (!empty($_POST['minscaledenom']) ? $_POST['minscaledenom'] : -1);
  $la->maxscaledenom = (!empty($_POST['maxscaledenom']) ? $_POST['maxscaledenom'] : -1);

  $c->addLabel($la);

  $la->free(); unset($la);
  $c->free(); unset($c);
  $l->free(); unset($l);

  $map->save($mapfile);
  $map->free(); unset($map);

  header('Location: layer-style-label.php?layer='.$_GET['layer'].'&class='.$_GET['class'].'&label');
  exit();
}
else if ($mapscript && isset($_GET['label-remove'])) {
  try {
    $map = new mapObj($mapfile);

    $l = $map->getLayer(intval($_GET['layer']));

    $c = $l->getClass(intval($_GET['class']));

    $c->removeLabel(intval($_GET['label-remove']));

    $c->free(); unset($c);
    $l->free(); unset($l);

    $map->save($mapfile);
    $map->free(); unset($map);
  } catch (MapScriptException $e) {
    $error = $e->getMessage();
  }

  header('Location: layer-style-label.php?layer='.$_GET['layer'].'&class='.$_GET['class'].'&label');
  exit();
}

$meta = mapfile_getmeta($mapfile);
$layers = mapfile_getlayers($mapfile);
$layer = $layers[intval($_GET['layer'])];
$class = $layer['class'][intval($_GET['class'])];
$style_json = json_encode($class['style']);
$label_json = json_encode($class['label']);

page_header('Layer: '.$layer['name'].' - Class: '.$class['name']);
?>
<div class="container">
  <h1>Map: <a href="index.php"><?= htmlentities($meta['name']) ?></a></h1>
  <h2>Layer: <a href="layer-class.php?layer=<?= intval($_GET['layer']) ?>"><?= htmlentities($layer['name']) ?></a></h2>
  <h3>Class: <?= htmlentities($class['name']) ?></h3>

  <div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"<?= (isset($_GET['style']) ? ' class="active"' : '') ?>><a href="#style" aria-controls="style" role="tab" data-toggle="tab"><i class="fa fa-paint-brush"></i> <?= count($class['style']) ?> style<?= (count($class['style']) > 1 ? 's' : '') ?></a></li>
      <li role="presentation"<?= (isset($_GET['label']) ? ' class="active"' : '') ?>><a href="#label" aria-controls="label" role="tab" data-toggle="tab"><i class="fa fa-tag"></i> <?= count($class['label']) ?> label<?= (count($class['label']) > 1 ? 's' : '') ?></a></li>
    </ul>

    <div class="tab-content">
      <div role="tabpanel" class="tab-pane<?= (isset($_GET['style']) ? ' active' : '') ?>" id="style">
        <table class="table table-striped">
          <thead>
            <tr>
              <th></th>
              <th colspan="2">Color</th>
              <th colspan="2">Outline Color</th>
              <th>Width</th>
              <th>Symbol</th>
              <th>Size</th>
              <th colspan="4"></th>
            </tr>
          </thead>
          <tbody>
        <?php
          foreach($class['style'] as $i => $s) {
            echo '<tr>';
              echo '<td class="text-right">'.($i+1).'.</td>';
              echo '<td style="min-width:20px;'.(array_sum($s['color']) >= 0 ? ' background-color:rgb('.implode(',', $s['color']).');' : '').'"></td>';
              echo '<td>'.(array_sum($s['color']) >= 0 ? implode(', ', $s['color']) : '').'</td>';
              echo '<td style="min-width:20px;'.(array_sum($s['outlinecolor']) >= 0 ? ' background-color:rgb('.implode(',', $s['outlinecolor']).');' : '').'"></td>';
              echo '<td>'.(array_sum($s['outlinecolor']) >= 0 ? implode(', ', $s['outlinecolor']) : '').'</td>';
              echo '<td>'.($s['width'] >= 0 ? $s['width'] : '').'</td>';
              echo '<td>'.$s['symbolname'].'</td>';
              echo '<td>'.($s['size'] >= 0 ? $s['size'] : '').'</td>';
              echo '<td class="text-center" style="width:20px;"><a href="#modal-style" data-toggle="modal" title="Edit"><i class="fa fa-pencil-square-o"></i></a></td>';
              echo '<td class="text-center" style="width:20px;">'.($i < (count($class['style'])-1) ? '<a href="?layer='.$_GET['layer'].'&amp;class='.$_GET['class'].'&amp;style-down='.$i.'" title="Move down"><i class="fa fa-arrow-down"></i></a>' : '').'</td>';
              echo '<td class="text-center" style="width:20px;">'.($i > 0 ? '<a href="?layer='.$_GET['layer'].'&amp;class='.$_GET['class'].'&amp;style-up='.$i.'" title="Move up"><i class="fa fa-arrow-up"></i></a>' : '').'</td>';
              echo '<td class="text-center" style="width:20px;"><a href="?layer='.$_GET['layer'].'&amp;class='.$_GET['class'].'&amp;style-remove='.$i.'" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
            echo '</tr>'.PHP_EOL;
          }
        ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="13" class="text-right"><a href="#modal-style" data-toggle="modal" style="text-decoration:none;"><i class="fa fa-plus-square"></i> Add new style</a></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <div role="tabpanel" class="tab-pane<?= (isset($_GET['label']) ? ' active' : '') ?>" id="label">
        <table class="table table-striped">
          <thead>
            <tr>
              <th></th>
              <th colspan="2">Color</th>
              <th colspan="2">Outline Color</th>
              <th>Size</th>
              <th>Position</th>
              <th colspan="2"></th>
            </tr>
          </thead>
          <tbody>
        <?php
          foreach($class['label'] as $i => $l) {
            echo '<tr>';
              echo '<td class="text-right">'.($i+1).'.</td>';
              echo '<td style="min-width:20px;'.(array_sum($l['color']) >= 0 ? ' background-color:rgb('.implode(',', $l['color']).');' : '').'"></td>';
              echo '<td>'.(array_sum($l['color']) >= 0 ? implode(', ', $l['color']) : '').'</td>';
              echo '<td style="min-width:20px;'.(array_sum($l['outlinecolor']) >= 0 ? ' background-color:rgb('.implode(',', $l['outlinecolor']).');' : '').'"></td>';
              echo '<td>'.(array_sum($l['outlinecolor']) >= 0 ? implode(', ', $l['outlinecolor']) : '').'</td>';
              echo '<td>'.$l['size'].'</td>';
              echo '<td>';
                switch($l['position']) {
                  case ($mapscript ? MS_AUTO : Layer::POSITION_AUTO): echo 'AUTO'; break;
                  case ($mapscript ? MS_UL   : Layer::POSITION_UL  ): echo 'Upper Left'; break;
                  case ($mapscript ? MS_UC   : Layer::POSITION_UC  ): echo 'Upper Center'; break;
                  case ($mapscript ? MS_UR   : Layer::POSITION_UR  ): echo 'Upper Right'; break;
                  case ($mapscript ? MS_CL   : Layer::POSITION_CL  ): echo 'Center Left'; break;
                  case ($mapscript ? MS_CC   : Layer::POSITION_CC  ): echo 'Center Center'; break;
                  case ($mapscript ? MS_CR   : Layer::POSITION_CR  ): echo 'Center Right'; break;
                  case ($mapscript ? MS_LL   : Layer::POSITION_LL  ): echo 'Lower Left'; break;
                  case ($mapscript ? MS_LC   : Layer::POSITION_LC  ): echo 'Lower Center'; break;
                  case ($mapscript ? MS_LR   : Layer::POSITION_LR  ): echo 'Lower Right'; break;
                  default: echo '<i class="text-warning">Unkown</i>'; break;
                }
              echo '</td>';
              echo '<td class="text-center" style="width:20px;"><a href="#modal-label" data-toggle="modal" title="Edit"><i class="fa fa-pencil-square-o"></i></a></td>';
              echo '<td class="text-center" style="width:20px;"><a href="?layer='.$_GET['layer'].'&amp;class='.$_GET['class'].'&amp;label-remove='.$i.'" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
            echo '</tr>'.PHP_EOL;
          }
        ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="11" class="text-right"><a href="#modal-label" data-toggle="modal" style="text-decoration:none;"><i class="fa fa-plus-square"></i> Add new label</a></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

</div>

<div id="modal-style" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <form action="layer-style-label.php?layer=<?= $_GET['layer'] ?>&class=<?= $_GET['class'] ?>" method="post" class="form-horizontal" autocomplete="off">
      <input type="hidden" name="style">
      <div class="modal-body">
        <div class="form-group">
          <label class="col-sm-3 control-label">Color</label>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputStyleColorR" name="color[r]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputStyleColorG" name="color[g]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputStyleColorB" name="color[b]"></div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">Outline color</label>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputStyleOutlineColorR" name="outlinecolor[r]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputStyleOutlineColorG" name="outlinecolor[g]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputStyleOutlineColorB" name="outlinecolor[b]"></div>
        </div>
        <div class="form-group">
          <label for="inputStyleWidth" class="col-sm-3 control-label">Width</label>
          <div class="col-sm-9">
            <input type="number" class="form-control" id="inputStyleWidth" name="width">
          </div>
        </div>
        <div class="form-group">
          <label for="inputStyleSymbol" class="col-sm-3 control-label">Symbol</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="inputStyleSymbol" name="symbolname">
          </div>
        </div>
        <div class="form-group">
          <label for="inputStyleSize" class="col-sm-3 control-label">Size</label>
          <div class="col-sm-9">
            <input type="number" class="form-control" id="inputStyleSize" name="size">
          </div>
        </div>
        <div class="form-group">
          <label for="inputStylePattern" class="col-sm-3 control-label">Pattern</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="inputStylePattern" name="pattern">
            <span class="help-block">List of on, off values separated by a space.</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="action" value="save-style">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

<div id="modal-label" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <form action="layer-style-label.php?layer=<?= $_GET['layer'] ?>&class=<?= $_GET['class'] ?>" method="post" class="form-horizontal" autocomplete="off">
      <input type="hidden" name="label">
      <div class="modal-body">
        <div class="form-group">
          <label for="selectLabelAlign" class="col-sm-3 control-label">Align</label>
          <div class="col-sm-9">
            <select class="form-control" id="selectLabelAlign" name="align">
              <option value="<?= MS_ALIGN_LEFT ?>">Left</option>
              <option value="<?= MS_ALIGN_CENTER ?>">Center</option>
              <option value="<?= MS_ALIGN_RIGHT ?>">Right</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="selectLabelPosition" class="col-sm-3 control-label">Position</label>
          <div class="col-sm-9">
            <select class="form-control" id="selectLabelPosition" name="position">
              <option value="<?= MS_AUTO ?>" selected="selected">AUTO</option>
              <option value="<?= MS_UL ?>">Upper Left</option>
              <option value="<?= MS_UC ?>">Upper Center</option>
              <option value="<?= MS_UR ?>">Upper Right</option>
              <option value="<?= MS_CL ?>">Center Left</option>
              <option value="<?= MS_CC ?>">Center Center</option>
              <option value="<?= MS_CR ?>">Center Right</option>
              <option value="<?= MS_LL ?>">Lower Left</option>
              <option value="<?= MS_LC ?>">Lower Center</option>
              <option value="<?= MS_LR ?>">Lower Right</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">Color</label>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputLabelColorR" name="color[r]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputLabelColorG" name="color[g]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputLabelColorB" name="color[b]"></div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">Outline color</label>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputLabelOutlineColorR" name="outlinecolor[r]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputLabelOutlineColorG" name="outlinecolor[g]"></div>
          <div class="col-sm-3"><input type="number" class="form-control" id="inputLabelOutlineColorB" name="outlinecolor[b]"></div>
        </div>
        <div class="form-group">
          <label for="inputLabelMinScaleDenom" class="col-sm-3 control-label">Min. Scale Denom.</label>
          <div class="col-sm-9">
            <input type="number" class="form-control" id="inputLabelMinScaleDenom" name="minscaledenom">
          </div>
        </div>
        <div class="form-group">
          <label for="inputLabelMaxScaleDenom" class="col-sm-3 control-label">Max. Scale Denom.</label>
          <div class="col-sm-9">
            <input type="number" class="form-control" id="inputLabelMaxScaleDenom" name="maxscaledenom">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="action" value="save-label">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
  var s = <?= $style_json ?>, l = <?= $label_json ?>;


  $(document).ready(function() {
    $('a.text-danger').on('click', function(event) { if (!confirm('Are you sure you want to delete this style/label ?')) { event.preventDefault(); } });

    $('#modal-style').on('show.bs.modal', function(event) {
      $(this).find('form')[0].reset();

      if ($(event.relatedTarget).has('.fa-plus-square').length > 0) {
        $(this).find('h4').html('<i class="fa fa-plus-square"></i> New style');
        $(this).find('input[name=style]').prop('disabled', true);
      } else {
        var i = $(event.relatedTarget).closest('tr').index();
        var _style = s[i];
        $(this).find('h4').html('<i class="fa fa-pencil-square-o"></i> Style #'+(i+1));
        $(this).find('input[name=style]').prop('disabled', false).val(i);
        $(this).find('#inputStyleColorR').val((_style.color.r >= 0 ? _style.color.r : ''));
        $(this).find('#inputStyleColorG').val((_style.color.g >= 0 ? _style.color.g : ''));
        $(this).find('#inputStyleColorB').val((_style.color.b >= 0 ? _style.color.b : ''));
        $(this).find('#inputStyleOutlineColorR').val((_style.outlinecolor.r >= 0 ? _style.outlinecolor.r : ''));
        $(this).find('#inputStyleOutlineColorG').val((_style.outlinecolor.g >= 0 ? _style.outlinecolor.g : ''));
        $(this).find('#inputStyleOutlineColorB').val((_style.outlinecolor.b >= 0 ? _style.outlinecolor.b : ''));
        $(this).find('#inputStyleWidth').val((_style.width >= 0 ? _style.width : ''));
        $(this).find('#inputStyleSymbol').val(_style.symbolname);
        $(this).find('#inputStyleSize').val((_style.size >= 0 ? _style.size : ''));
        $(this).find('#inputStylePattern').val(_style.pattern.join(' '));
      }
    });

    $('#modal-label').on('show.bs.modal', function(event) {
      $(this).find('form')[0].reset();

      if ($(event.relatedTarget).has('.fa-plus-square').length > 0) {
        $(this).find('h4').html('<i class="fa fa-plus-square"></i> New label');
        $(this).find('input[name=label]').prop('disabled', true);
      } else {
        var i = $(event.relatedTarget).closest('tr').index();
        var _label = l[i];
        $(this).find('h4').html('<i class="fa fa-pencil-square-o"></i> Label #'+(i+1));
        $(this).find('input[name=label]').prop('disabled', false).val(i);
        $(this).find('#selectLabelAlign').val(_label.align);
        $(this).find('#selectLabelPosition').val(_label.position);
        $(this).find('#inputLabelColorR').val((_label.color.r >= 0 ? _label.color.r : ''));
        $(this).find('#inputLabelColorG').val((_label.color.g >= 0 ? _label.color.g : ''));
        $(this).find('#inputLabelColorB').val((_label.color.b >= 0 ? _label.color.b : ''));
        $(this).find('#inputLabelOutlineColorR').val((_label.outlinecolor.r >= 0 ? _label.outlinecolor.r : ''));
        $(this).find('#inputLabelOutlineColorG').val((_label.outlinecolor.g >= 0 ? _label.outlinecolor.g : ''));
        $(this).find('#inputLabelOutlineColorB').val((_label.outlinecolor.b >= 0 ? _label.outlinecolor.b : ''));
        $(this).find('#inputLabelMinScaleDenom').val((_label.minscaledenom >= 0 ? _label.minscaledenom : ''));
        $(this).find('#inputLabelMaxScaleDenom').val((_label.maxscaledenom >= 0 ? _label.maxscaledenom : ''));
      }
    });
  });
</script>

<?php
page_footer();