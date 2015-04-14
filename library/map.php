<?php
/**
 * MapFile Generator - MapServer .MAP Generator (Read, Write & Preview).
 * PHP Version 5.3+
 * @link https://github.com/jbelien/MapFile-Generator
 * @author Jonathan Beliën <jbe@geo6.be>
 * @copyright 2015 Jonathan Beliën
 * @license GNU General Public License, version 2
 * @note This project is still in development. Please use with caution !
 */
namespace MapFile;

require_once('class.php');
require_once('exception.php');
require_once('label.php');
require_once('layer.php');
require_once('legend.php');
require_once('scalebar.php');
require_once('style.php');

/**
 * MapFile Generator - Map (MAP) Class.
 * [MapFile MAP clause](http://mapserver.org/mapfile/map.html).
 * @package MapFile
 * @author Jonathan Beliën <jbe@geo6.be>
 * @link http://mapserver.org/mapfile/map.html
 * @uses \MapFile\Legend
 * @uses \MapFile\Scalebar
 */
class Map {
  const STATUS_ON = 1;
  const STATUS_OFF = 0;

  const UNITS_INCHES = 0;
  const UNITS_FEET = 1;
  const UNITS_MILES = 2;
  const UNITS_METERS = 3;
  const UNITS_KILOMETERS = 4;
  const UNITS_DD = 5;
  const UNITS_PIXELS = 6;
  const UNITS_NAUTICALMILES = 8;

  /** @var string Path to fontset file. */
  private $fontsetfilename;
  /** @var string Path to symbolset file. */
  private $symbolsetfilename;
  /** @var string[] List of metadata's. */
  private $metadata = array();

  /** @var \MapFile\Layer[] List of layers. */
  private $_layers = array();

  /** @var float[] Spatial extent.*/
  public $extent = array(-1, -1, -1, -1);
  /** @var integer Size Y (height) in pixels of the output image. */
  public $height = 500;
  /** @var string MapFile name. */
  public $name = 'MYMAP';
  /**
  * @var string MapFile EPSG Projection.
  * @link http://spatialreference.org/ref/epsg/
  */
  public $projection;
  /**
  * @var integer MapFile Status (Is the map active ?).
  * @note Use :
  * * self::STATUS_ON
  * * self::STATUS_OFF
  */
  public $status = self::STATUS_ON;
  /**
  * @var integer Units of the map coordinates.
  * @note Use :
  * * self::UNITS_INCHES
  * * self::UNITS_FEET
  * * self::UNITS_MILES
  * * self::UNITS_METERS
  * * self::UNITS_KILOMETERS
  * * self::UNITS_DD
  * * self::UNITS_PIXELS
  * * self::UNITS_NAUTICALMILES
  */
  public $units = self::UNITS_METERS;
  /** @var integer Size X (width) in pixels of the output image. */
  public $width = 500;

  /**
  * @var \MapFile\Legend Map Legend object.
  */
  public $legend;
  /**
  * @var \MapFile\Scalebar Map Scalebar object.
  */
  public $scalebar;

  /**
  * Constructor.
  * @param string $mapfile Path to a valid .map MapFile.
  */
  public function __construct($mapfile = NULL) {
    if (!is_null($mapfile) && file_exists($mapfile)) $this->read($mapfile);

    if (is_null($this->legend)) $this->legend = new Legend();
    if (is_null($this->scalebar)) $this->scalebar = new Scalebar();
  }

  /**
  * Set the `extent` property.
  * @param float $minx
  * @param float $miny
  * @param float $maxx
  * @param float $maxy
  */
  public function setExtent($minx, $miny, $maxx, $maxy) {
    $this->extent = array($minx, $miny, $maxx, $maxy);
  }
  /**
  * Set the `fontsetfilename` property.
  * @param string $filename Path to a valid fontset file.
  */
  public function setFontSet($filename) {
    if (file_exists($filename)) $this->fontsetfilename = $filename; else throw new Exception('FontSet file does not exists.');
  }
  /**
  * Set a `metadata` property.
  * @param string $key
  * @param string $value
  */
  public function setMetadata($key, $value) {
    $this->metadata[$key] = $value;
  }
  /**
  * Set `height` and `width` properties.
  * @param integer $width Width in pixels of the output image.
  * @param integer $height Height in pixels of the output image.
  */
  public function setSize($width, $height) {
    $this->width = intval($width);
    $this->height = intval($height);
  }
  /**
  * Set the `symbolsetfilename` property.
  * @param string $filename Path to a valid symbolset file.
  */
  public function setSymbolSet($filename) {
    if (file_exists($filename)) $this->symbolsetfilename = $filename; else throw new Exception('SymbolSet file does not exists.');
  }

  /**
  * Return the list of the layers.
  * @return \MapFile\Layer[]
  */
  public function getLayers() {
    return $this->_layers;
  }
  /**
  * Return the layer matching the index sent as parameter.
  * @param integer $i Layer Index.
  * @return \MapFile\Layer|false false if the index is not found.
  */
  public function getLayer($i) {
    return (isset($this->_layers[$i]) ? $this->_layers[$i] : FALSE);
  }
  /**
  * Return the metadata matching the key sent as parameter.
  * @param string $key Metadata Key.
  * @return string|false false if the key is not found
  */
  public function getMetadata($key) {
    return (isset($this->metadata[$key]) ? $this->metadata[$key] : FALSE);
  }

  /**
  * Remove the metadata matching the key sent as parameter.
  * @param string $key Metadata Key.
  */
  public function removeMetadata($key) {
    if (isset($this->metadata[$key])) unset($this->metadata[$key]);
  }

  /**
  * Add a new \MapFile\Layer to the MapFile.
  * @param \MapFile\Layer $layer New Layer.
  * @return \MapFile\Layer New layer.
  */
  public function addLayer($layer = NULL) {
    if (is_null($layer)) $layer = new Layer();
    $count = array_push($this->_layers, $layer);
    return $this->_layers[$count-1];
  }

  /**
  * Write the \MapFile\Map object to a MapFile.
  * @param string $filename Path to the new MapFile.
  * @uses \MapFile\Layer::write()
  * @uses \MapFile\Legend::write()
  * @uses \MapFile\Scalebar::write()
  */
  public function save($filename) {
    $f = fopen($filename, 'w');
    fwrite($f, 'MAP'.PHP_EOL);

    fwrite($f, '  STATUS '.self::convertStatus($this->status).PHP_EOL);
    fwrite($f, '  NAME "'.$this->name.'"'.PHP_EOL);
    if (!empty($this->extent) && array_sum($this->extent) >= 0) fwrite($f, '  EXTENT '.implode(' ',$this->extent).PHP_EOL);
    if (!empty($this->fontsetfilename)) fwrite($f, '  FONTSET "'.$this->fontsetfilename.'"'.PHP_EOL);
    if (!empty($this->symbolsetfilename)) fwrite($f, '  SYMBOLSET "'.$this->symbolsetfilename.'"'.PHP_EOL);
    if (!empty($this->width) && !empty($this->height)) fwrite($f, '  SIZE '.$this->width.' '.$this->height.PHP_EOL);
    if (!is_null($this->units)) fwrite($f, '  UNITS '.self::convertUnits($this->units).PHP_EOL);

    if (!empty($this->projection)) {
      fwrite($f, PHP_EOL);
      fwrite($f, '  PROJECTION'.PHP_EOL);
      fwrite($f, '    "init='.strtolower($this->projection).'"'.PHP_EOL);
      fwrite($f, '  END # PROJECTION'.PHP_EOL);
    }

    fwrite($f, PHP_EOL);
    fwrite($f, '  WEB'.PHP_EOL);
    if (!empty($this->metadata)) {
      fwrite($f, '    METADATA'.PHP_EOL);
      foreach ($this->metadata as $k => $v) fwrite($f, '      "'.$k.'" "'.$v.'"'.PHP_EOL);
      fwrite($f, '    END # METADATA'.PHP_EOL);
    }
    fwrite($f, '  END # WEB'.PHP_EOL);

    fwrite($f, PHP_EOL);
    fwrite($f, $this->legend->write());

    fwrite($f, PHP_EOL);
    fwrite($f, $this->scalebar->write());

    foreach ($this->_layers as $layer) {
      fwrite($f, PHP_EOL);
      fwrite($f, $layer->write());
    }

    fwrite($f, 'END # MAP'.PHP_EOL);
    fclose($f);
  }

  /**
  * Read a valid MapFile.
  * @param string $mapfile Path to the MapFile to read.
  * @uses \MapFile\Layer::read()
  * @uses \MapFile\Legend::read()
  * @uses \MapFile\Scalebar::read()
  */
  private function read($mapfile) {
    $map = FALSE; $map_projection = FALSE; $map_outputformat = FALSE; $map_querymap = FALSE; $map_legend = FALSE; $map_scalebar = FALSE; $map_layer = FALSE; $map_web = FALSE; $map_metadata = FALSE;

    $h = fopen($mapfile, 'r');
    while (($_sz = fgets($h, 1024)) !== false) {
      $sz = trim($_sz);

      if (preg_match('/^MAP$/i', $sz)) $map = TRUE;
      else if ($map && preg_match('/^END( # MAP)?$/i', $sz)) $map = FALSE;

      else if ($map && preg_match('/^OUTPUTFORMAT$/i', $sz)) $map_outputformat = TRUE;
      else if ($map && $map_outputformat && preg_match('/^END( # OUTPUTFORMAT)?$/i', $sz)) $map_outputformat = FALSE;
      else if ($map && $map_outputformat) continue;

      else if ($map && preg_match('/^QUERYMAP$/i', $sz)) $map_querymap = TRUE;
      else if ($map && $map_querymap && preg_match('/^END( # QUERYMAP)?$/i', $sz)) $map_querymap = FALSE;
      else if ($map && $map_querymap) continue;

      else if ($map && preg_match('/^PROJECTION$/i', $sz)) $map_projection = TRUE;
      else if ($map && $map_projection && preg_match('/^END( # PROJECTION)?$/i', $sz)) $map_projection = FALSE;
      else if ($map && $map_projection && preg_match('/^"init=(.+)"$/i', $sz, $matches)) $this->projection = $matches[1];

      else if ($map && preg_match('/^LEGEND$/i', $sz)) { $map_legend = TRUE; $legend[] = $sz; }
      else if ($map && $map_legend && preg_match('/^END( # LEGEND)?$/i', $sz)) { $legend[] = $sz; $this->legend = new Legend($legend); $map_legend = FALSE; unset($legend); }
      else if ($map && $map_legend) { $legend[] = $sz; }

      else if ($map && preg_match('/^SCALEBAR$/i', $sz)) { $map_scalebar = TRUE; $scalebar[] = $sz; }
      else if ($map && $map_scalebar && preg_match('/^END( # SCALEBAR)?$/i', $sz)) { $scalebar[] = $sz; $this->scalebar = new Scalebar($scalebar); $map_scalebar = FALSE; unset($scalebar); }
      else if ($map && $map_scalebar) { $scalebar[] = $sz; }

      else if ($map && preg_match('/^LAYER$/i', $sz)) { $map_layer = TRUE; $layer[] = $sz; }
      else if ($map && $map_layer && preg_match('/^END( # LAYER)?$/i', $sz)) { $layer[] = $sz; $this->addLayer(new Layer($layer)); $map_layer = FALSE; unset($layer); }
      else if ($map && $map_layer) { $layer[] = $sz; }

      else if ($map && preg_match('/^WEB$/i', $sz)) { $map_web = TRUE; }
      else if ($map && $map_web && preg_match('/^END( # WEB)?$/i', $sz)) { $map_web = FALSE; }
      else if ($map && $map_web && preg_match('/^METADATA$/i', $sz)) { $map_metadata = TRUE; }
      else if ($map && $map_web && $map_metadata && preg_match('/^END( # METADATA)?$/i', $sz)) { $map_metadata = FALSE; }
      else if ($map && $map_web && $map_metadata && preg_match('/^"(.+)"\s"(.+)"$/i', $sz, $matches)) { $this->metadata[$matches[1]] = $matches[2]; }

      else if ($map && preg_match('/^NAME "(.+)"$/i', $sz, $matches)) $this->name = $matches[1];
      else if ($map && preg_match('/^STATUS (.+)$/i', $sz, $matches)) $this->status = self::convertStatus($matches[1]);
      else if ($map && preg_match('/^EXTENT ([0-9\.]+) ([0-9\.]+) ([0-9\.]+) ([0-9\.]+)$/i', $sz, $matches)) $this->extent = array($matches[1], $matches[2], $matches[3], $matches[4]);
      else if ($map && preg_match('/^FONTSET "(.+)"$/i', $sz, $matches)) $this->fontsetfilename = $matches[1];
      else if ($map && preg_match('/^SYMBOLSET "(.+)"$/i', $sz, $matches)) $this->symbolsetfilename = $matches[1];
      else if ($map && preg_match('/^SIZE ([0-9]+) ([0-9]+)$/i', $sz, $matches)) $this->size = array($matches[1], $matches[2]);

      else if ($map && preg_match('/^UNITS (.+)$/i', $sz, $matches)) $this->units = self::convertUnits($matches[1]);
    }
    fclose($h);
  }

  /**
  * Convert `status` property to the text value or to the constant matching the text value.
  * @param string|integer $s
  * @return integer|string
  */
  private static function convertStatus($s = NULL) {
    $statuses = array(
      self::STATUS_ON  => 'ON',
      self::STATUS_OFF => 'OFF'
    );

    if (is_numeric($s)) return (isset($statuses[$s]) ? $statuses[$s] : FALSE);
    else return array_search($s, $statuses);
  }
  /**
  * Convert `units` property to the text value or to the constant matching the text value.
  * @param string|integer $u
  * @return integer|string
  */
  private static function convertUnits($u = NULL) {
    $units = array(
      self::UNITS_INCHES        => 'INCHES',
      self::UNITS_FEET          => 'FEET',
      self::UNITS_MILES         => 'MILES',
      self::UNITS_METERS        => 'METERS',
      self::UNITS_KILOMETERS    => 'KILOMETERS',
      self::UNITS_DD            => 'DD',
      self::UNITS_PIXELS        => 'PIXELS',
      self::UNITS_NAUTICALMILES => 'NAUTICALMILES'
    );

    if (is_numeric($u)) return (isset($units[$u]) ? $units[$u] : FALSE);
    else return array_search($u, $units);
  }
}