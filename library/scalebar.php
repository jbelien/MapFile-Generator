<?php
namespace MapFile;

class Scalebar {
  private $color = array(0,0,0);
  private $outlinecolor = array(0,0,0);

  public $intervals = 4;
  public $status = self::STATUS_OFF;
  public $units = self::UNITS_METERS;

  public $label;

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

  public function __construct($scalebar = NULL) {
    if (!is_null($scalebar)) $this->read($scalebar);

    if (is_null($this->label)) $this->label = new Label();
  }

  public function setColor($r,$g,$b) {
    if ($r >= 0 && $r <= 255 && $g >= 0 && $g <= 255 && $b >= 0 && $b <= 255)
      $this->color = array($r,$g,$b);
    else
      throw new Exception('Invalid SCALEBAR COLOR('.$r.' '.$g.' '.$b.').');
  }
  public function setOutlineColor($r,$g,$b) {
    if ($r >= 0 && $r <= 255 && $g >= 0 && $g <= 255 && $b >= 0 && $b <= 255)
      $this->outlinecolor = array($r,$g,$b);
    else
      throw new Exception('Invalid SCALEBAR OUTLINECOLOR('.$r.' '.$g.' '.$b.').');
  }

  public function write() {
    $scalebar  = '  SCALEBAR'.PHP_EOL;
    $scalebar .= '    STATUS '.$this->convertStatus().PHP_EOL;
    if (!is_null($this->units)) $scalebar .= '    UNITS '.$this->convertUnits().PHP_EOL;
    if (!empty($this->color) && array_sum($this->color) >= 0) $scalebar .= '    COLOR '.implode(' ',$this->color).PHP_EOL;
    if (!empty($this->outlinecolor) && array_sum($this->outlinecolor) >= 0) $scalebar .= '    OUTLINECOLOR '.implode(' ',$this->outlinecolor).PHP_EOL;
    if (!empty($this->intervals)) $scalebar .= '    INTERVALS '.intval($this->intervals).PHP_EOL;
    $scalebar .= $this->label->write(2);
    $scalebar .= '  END # SCALEBAR'.PHP_EOL;

    return $scalebar;
  }

  private function read($array) {
    $scalebar = FALSE; $scalebar_label = FALSE;

    foreach ($array as $_sz) {
      $sz = trim($_sz);

      if (preg_match('/^SCALEBAR$/i', $sz)) $scalebar = TRUE;
      else if ($scalebar && preg_match('/^END( # SCALEBAR)?$/i', $sz)) $scalebar = FALSE;

      else if ($scalebar && preg_match('/^LABEL$/i', $sz)) { $scalebar_label = TRUE; $label[] = $sz; }
      else if ($scalebar && $scalebar_label && preg_match('/^END( # LABEL)?$/i', $sz)) { $label[] = $sz; $this->label = new Label($label); $scalebar_label = FALSE; unset($label); }
      else if ($scalebar && $scalebar_label) { $label[] = $sz; }

      else if ($scalebar && preg_match('/^STATUS (.+)$/i', $sz, $matches)) $this->status = self::convertStatus($matches[1]);
      else if ($scalebar && preg_match('/^INTERVALS ([0-9]+)$/i', $sz, $matches)) $this->intervals = $matches[1];
      else if ($scalebar && preg_match('/^COLOR ([0-9]+) ([0-9]+) ([0-9]+)$/i', $sz, $matches)) $this->color = array($matches[1], $matches[2], $matches[3]);
      else if ($scalebar && preg_match('/^OUTLINECOLOR ([0-9]+) ([0-9]+) ([0-9]+)$/i', $sz, $matches)) $this->outlinecolor = array($matches[1], $matches[2], $matches[3]);
      else if ($scalebar && preg_match('/^UNITS (.+)$/i', $sz, $matches)) $this->units = self::convertUnits($matches[1]);
    }
  }

  private function convertStatus($s = NULL) {
    $statuses = array(
      self::STATUS_ON  => 'ON',
      self::STATUS_OFF => 'OFF'
    );

    if (is_null($s)) return $statuses[$this->status];
    else if (is_numeric($s)) return (isset($statuses[$s]) ? $statuses[$s] : FALSE);
    else return array_search($s, $statuses);
  }
  private function convertUnits($u = NULL) {
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

    if (is_null($u)) return $units[$this->units];
    else if (is_numeric($u)) return (isset($units[$u]) ? $units[$u] : FALSE);
    else return array_search($u, $units);
  }
}