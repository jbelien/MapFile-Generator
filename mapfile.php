<?php
session_start();

$settings = parse_ini_file('settings.ini');
$mapscript = extension_loaded('mapscript');

require('fn.php');

$source = NULL; $mapfile = NULL;
if (isset($_SESSION['mapfile-generator']['source']) && file_exists($_SESSION['mapfile-generator']['source'])) $source = $_SESSION['mapfile-generator']['source'];
if (isset($_SESSION['mapfile-generator']['mapfile']) && file_exists($_SESSION['mapfile-generator']['mapfile'])) $mapfile = $_SESSION['mapfile-generator']['mapfile'];

if (/*is_null($source) || */is_null($mapfile)) { header('Location:index.php'); exit(); }

if (isset($_GET['save'], $_SESSION['mapfile-generator']['mapfile'], $_SESSION['mapfile-generator']['source'])) {
  copy($_SESSION['mapfile-generator']['source'], $_SESSION['mapfile-generator']['source'].'.bak');
  copy($_SESSION['mapfile-generator']['mapfile'], $_SESSION['mapfile-generator']['source']);
}
else if (isset($_GET['export'], $_SESSION['mapfile-generator']['mapfile'])) {
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
else if (isset($_POST['text'])) {
  file_put_contents($_SESSION['mapfile-generator']['mapfile'], $_POST['text']);
}

page_header();

if (isset($_GET['edit'])) {
?>
<div class="container">
  <form action="mapfile.php" method="post">
    <div class="text-center">
      <button id="mapfile-save" type="submit" class="btn btn-primary"><i class="fa fa-floppy-o"></i> Save</button>
      <a id="mapfile-cancel" class="btn btn-default" href="mapfile.php"><i class="fa fa-backward"></i> Cancel</a>
    </div>

    <?php if (isset($_SESSION['mapfile-generator']['source'])) echo '<p class="text-info">Source : <samp>'.$_SESSION['mapfile-generator']['source'].'</samp></p>'; ?>
    <p class="text-info">Last update : <?= (isset($_SESSION['mapfile-generator']['source']) ? strftime('%e %B %Y %H:%M', filemtime($_SESSION['mapfile-generator']['source'])) : strftime('%e %B %Y %H:%M', filemtime($_SESSION['mapfile-generator']['mapfile']))) ?></p>

    <textarea class="form-control" name="text" style="resize:vertical; min-height:500px;"><?= file_get_contents($_SESSION['mapfile-generator']['mapfile']) ?></textarea>
  </form>
</div>
<?php } else { ?>
<div class="container">
  <div class="text-center">
    <button id="mapfile-open" class="btn btn-default" data-toggle="modal" data-target="#modal-open"><i class="fa fa-folder-open-o"></i> Open</button>
    <a id="mapfile-edit" class="btn btn-warning" href="?edit"><i class="fa fa-pencil-square-o"></i> Edit</a>
    <?php if (isset($_SESSION['mapfile-generator']['source'])) echo '<a id="mapfile-save" class="btn btn-primary" href="?map='.$_SESSION['mapfile-generator']['source'].'&amp;save"><i class="fa fa-floppy-o"></i> Save</a>'; ?>
    <a id="mapfile-export" class="btn btn-default" href="?export"><i class="fa fa-download"></i> Export</a>
  </div>

  <?php if (isset($_SESSION['mapfile-generator']['source'])) echo '<p class="text-info">Source : <samp>'.$_SESSION['mapfile-generator']['source'].'</samp></p>'; ?>
  <p class="text-info">Last update : <?= (isset($_SESSION['mapfile-generator']['source']) ? strftime('%e %B %Y %H:%M', filemtime($_SESSION['mapfile-generator']['source'])) : strftime('%e %B %Y %H:%M', filemtime($_SESSION['mapfile-generator']['mapfile']))) ?></p>

  <pre><?= file_get_contents($_SESSION['mapfile-generator']['mapfile']) ?></pre>
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

          echo '<a href="index.php?map='.$dir.'/'.$file.'">'.$file.'</a><br>';
        }
      closedir($dh);
      ?>
      </div>
    </div>
  </div>
</div>
<?php
}
page_footer();