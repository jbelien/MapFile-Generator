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

if (is_null($source) || is_null($mapfile) || !isset($_GET['layer']) || !isset($_GET['class'])) { header('Location:index.php'); exit(); }

$meta = mapfile_getmeta($mapfile);
$layers = mapfile_getlayers($mapfile);
$layer = $layers[intval($_GET['layer'])];
$class = $layer['class'][intval($_GET['class'])];

page_header('Layer: '.$layer['name'].' - Class: '.$class['name']);
?>
<div class="container">
  <h1>Map: <a href="index.php"><?= htmlentities($meta['name']) ?></a></h1>
  <h2>Layer: <a href="layer-class.php?layer=<?= intval($_GET['layer']) ?>"><?= htmlentities($layer['name']) ?></a></h2>
  <h3>Class: <?= htmlentities($class['name']) ?></h3>

  <div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"<?= (isset($_GET['style']) ? ' class="active"' : '') ?>><a href="#style" aria-controls="style" role="tab" data-toggle="tab"><i class="fa fa-paint-brush"></i> <?= count($class['style']) ?> style<?= (count($class['style']) > 1 ? 's' : '') ?></a></li>
      <li role="presentation"<?= (isset($_GET['label']) ? ' class="active"' : '') ?>><a href="#label" aria-controls="label" role="tab" data-toggle="tab"><i class="fa fa-font"></i> <?= count($class['label']) ?> label<?= (count($class['label']) > 1 ? 's' : '') ?></a></li>
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
              <th colspan="2"></th>
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
              echo '<td>'.$s['width'].'</td>';
              echo '<td>'.$s['symbolname'].'</td>';
              echo '<td>'.($s['size'] >= 0 ? $s['size'] : '').'</td>';
              echo '<td style="width:20px; text-align:center;"><a href="#"" title="Edit"><i class="fa fa-pencil-square-o"></i></a></td>';
              echo '<td style="width:20px; text-align:center;"><a href="#" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
            echo '</tr>'.PHP_EOL;
          }
        ?>
          </tbody>
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
              echo '<td>'.$l['position'].'</td>';
              echo '<td style="width:20px; text-align:center;"><a href="#"" title="Edit"><i class="fa fa-pencil-square-o"></i></a></td>';
              echo '<td style="width:20px; text-align:center;"><a href="#" class="text-danger" title="Remove"><i class="fa fa-trash-o"></i></a></td>';
            echo '</tr>'.PHP_EOL;
          }
        ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
<?php
page_footer();