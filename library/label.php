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

/**
 * MapFile Generator - Label (LABEL) Class.
 * [MapFile LABEL clause](http://mapserver.org/mapfile/label.html).
 * @package MapFile
 * @author Jonathan Beliën <jbe@geo6.be>
 * @link http://mapserver.org/mapfile/label.html
 */
class Label {
  const ALIGN_LEFT = 0;
  const ALIGN_CENTER = 1;
  const ALIGN_RIGHT = 2;

  const POSITION_UL = 101;
  const POSITION_LR = 102;
  const POSITION_UR = 103;
  const POSITION_LL = 104;
  const POSITION_CR = 105;
  const POSITION_CL = 106;
  const POSITION_UC = 107;
  const POSITION_LC = 108;
  const POSITION_CC = 109;
  const POSITION_XY = 111;
  const POSITION_AUTO = 110;
  const POSITION_AUTO2 = 114;
  const POSITION_FOLLOW = 112;
  const POSITION_NONE = 113;

  const SIZE_TINY = 0;
  const SIZE_SMALL = 1;
  const SIZE_MEDIUM = 2;
  const SIZE_LARGE = 3;
  const SIZE_GIANT = 4;

  const TYPE_TRUETYPE = 0;
  const TYPE_BITMAP = 1;

  /**
  * @var integer[] Color (RGB Format).
  * @note Index `0` = Red [0-255], Index `1` = Green [0-255], Index `2` = Blue [0-255]
  */
  private $color = array(0, 0, 0);
  /**
  * @var integer[] Outline color (RGB Format).
  * @note Index `0` = Red [0-255], Index `1` = Green [0-255], Index `2` = Blue [0-255]
  */
  private $outlinecolor;

  /**
  * @var integer Text alignment for multiline labels.
  * @note Use :
  * * self::ALIGN_LEFT
  * * self::ALIGN_CENTER
  * * self::ALIGN_RIGHT
  */
  public $align;
  /** @var string Font name (must be defined in fontset file) */
  public $font;
  /**
  * @var float Maximum scale denominator.
  * @see http://geography.about.com/cs/maps/a/mapscale.htm
  */
  public $maxscaledenom;
  /**
  * @var float Minimum scale denominator.
  * @see http://geography.about.com/cs/maps/a/mapscale.htm
  */
  public $minscaledenom;
  /**
  * @var integer Position of the label relative to the labeling point.
  * @note Use :
  * * self::POSITION_UL
  * * self::POSITION_UL
  * * self::POSITION_LR
  * * self::POSITION_UR
  * * self::POSITION_LL
  * * self::POSITION_CR
  * * self::POSITION_CL
  * * self::POSITION_UC
  * * self::POSITION_LC
  * * self::POSITION_CC
  * * self::POSITION_XY
  * * self::POSITION_AUTO
  * * self::POSITION_AUTO2
  * * self::POSITION_FOLLOW
  * * self::POSITION_NONE
  */
  public $position;
  /**
  * Text size (in pixels).
  * * If the \MapFile\Label::$type is defined as TYPE_TRUETYPE, \MapFile\Label::$size is the text size defined in pixels,
  * * If the \MapFile\Label::$type is defined as TYPE_BITMAP, \MapFile\Label::$size is a constant (see the note).
  * @var float|integer
  * @note Use :
  * * self::SIZE_TINY
  * * self::SIZE_SMALL
  * * self::SIZE_MEDIUM
  * * self::SIZE_LARGE
  * * self::SIZE_GIANT
  */
  public $size = self::SIZE_MEDIUM;
  /**
  * @var integer Type of font to use
  * @note Use :
  * * self::TYPE_TRUETYPE
  * * self::TYPE_BITMAP
  */
  public $type = self::TYPE_BITMAP;

  /**
  * Constructor.
  * @param string[] $label Array containing MapFile LABEL clause.
  * @todo Must read a MapFile LABEL clause without passing by an Array.
  */
  public function __construct($label = NULL) {
    if (!is_null($label)) $this->read($label);
  }

  /**
  * Set the `color` property.
  * @param integer $r Red component [0-255].
  * @param integer $g Green component [0-255].
  * @param integer $b Blue component [0-255].
  * @throws \MapFile\Exception if any component is lower < 0 or > 255
  */
  public function setColor($r,$g,$b) {
    if ($r >= 0 && $r <= 255 && $g >= 0 && $g <= 255 && $b >= 0 && $b <= 255)
      $this->color = array($r,$g,$b);
    else
      throw new Exception('Invalid LABEL COLOR('.$r.' '.$g.' '.$b.').');
  }
  /**
  * Set the `outlinecolor` property.
  * @param integer $r Red component [0-255].
  * @param integer $g Green component [0-255].
  * @param integer $b Blue component [0-255].
  * @throws \MapFile\Exception if any component is lower < 0 or > 255
  */
  public function setOutlineColor($r,$g,$b) {
    if ($r >= 0 && $r <= 255 && $g >= 0 && $g <= 255 && $b >= 0 && $b <= 255)
      $this->outlinecolor = array($r,$g,$b);
    else
      throw new Exception('Invalid LABEL OUTLINECOLOR('.$r.' '.$g.' '.$b.').');
  }

  /**
  * Get the `color` property.
  * @return integer[]
  */
  public function getColor() {
    return array('r' => $this->color[0], 'g' => $this->color[1], 'b' => $this->color[2]);
  }
  /**
  * Get the `outlinecolor` property.
  * @return integer[]
  */
  public function getOutlineColor() {
    return array('r' => $this->outlinecolor[0], 'g' => $this->outlinecolor[1], 'b' => $this->outlinecolor[2]);
  }

  /**
  * Unset `color` property.
  */
  public function unsetColor() {
    $this->color = NULL;
  }
  /**
  * Unset `outlinecolor` property.
  */
  public function unsetOutlineColor() {
    $this->outlinecolor = NULL;
  }

  /**
  * Write a valid MapFile LABEL clause.
  * @param integer $indent Level of indentation.
  * @return string
  */
  public function write($indent = 0) {
    $label  = str_repeat(' ', $indent*2).'LABEL'.PHP_EOL;
    $label .= str_repeat(' ', $indent*2).'  TYPE '.self::convertType($this->type).PHP_EOL;
    if ($this->type == self::TYPE_TRUETYPE && !empty($this->font)) $label .= str_repeat(' ', $indent*2).'  FONT "'.$this->font.'"'.PHP_EOL;
    if ($this->type == self::TYPE_BITMAP) $label .= str_repeat(' ', $indent*2).'  SIZE '.self::convertSize($this->size).PHP_EOL;
    else if ($this->type == self::TYPE_TRUETYPE) $label .= str_repeat(' ', $indent*2).'  SIZE '.floatval($this->size).PHP_EOL;
    if (!is_null($this->align) && strlen($this->align) > 0) $label .= str_repeat(' ', $indent*2).'  ALIGN '.self::convertAlign($this->align).PHP_EOL;
    if (!is_null($this->position) && strlen($this->position) > 0) $label .= str_repeat(' ', $indent*2).'  POSITION '.self::convertPosition($this->position).PHP_EOL;
    if (!is_null($this->minscaledenom)) $label .= '        MINSCALEDENOM '.floatval($this->minscaledenom).PHP_EOL;
    if (!is_null($this->maxscaledenom)) $label .= '        MAXSCALEDENOM '.floatval($this->maxscaledenom).PHP_EOL;
    if (!empty($this->color) && count($this->color) == 3 && array_sum($this->color) >= 0) $label .= str_repeat(' ', $indent*2).'  COLOR '.implode(' ',$this->color).PHP_EOL;
    if (!empty($this->outlinecolor) && count($this->outlinecolor) == 3 && array_sum($this->outlinecolor) >= 0) $label .= str_repeat(' ', $indent*2).'  OUTLINECOLOR '.implode(' ',$this->outlinecolor).PHP_EOL;
    $label .= str_repeat(' ', $indent*2).'END # LABEL'.PHP_EOL;

    return $label;
  }

  /**
  * Read a valid MapFile LABEL clause (as array).
  * @param string[] $array MapFile LABEL clause splitted in an array.
  * @todo Must read a MapFile LABEL clause without passing by an Array.
  */
  private function read($array) {
    $label = FALSE;

    foreach ($array as $_sz) {
      $sz = trim($_sz);

      if (preg_match('/^LABEL$/i', $sz)) $label = TRUE;
      else if ($label && preg_match('/^END( # LABEL)?$/i', $sz)) $label = FALSE;

      else if ($label && preg_match('/^TYPE (.+)$/i', $sz, $matches)) $this->type = self::convertType($matches[1]);
      else if ($label && preg_match('/^FONT "(.+)"$/i', $sz, $matches)) $this->font = $matches[1];
      else if ($label && preg_match('/^SIZE ([0-9]+)$/i', $sz, $matches)) $this->size = $matches[1];
      else if ($label && preg_match('/^SIZE (.+)$/i', $sz, $matches)) $this->size = self::convertSize($matches[1]);
      else if ($label && preg_match('/^ALIGN (.+)$/i', $sz, $matches)) $this->align = self::convertAlign($matches[1]);
      else if ($label && preg_match('/^COLOR ([0-9]+) ([0-9]+) ([0-9]+)$/i', $sz, $matches)) $this->color = array($matches[1], $matches[2], $matches[3]);
      else if ($label && preg_match('/^OUTLINECOLOR ([0-9]+) ([0-9]+) ([0-9]+)$/i', $sz, $matches)) $this->outlinecolor = array($matches[1], $matches[2], $matches[3]);
      else if ($label && preg_match('/^MINSCALEDENOM ([0-9\.]+)$/i', $sz, $matches)) $this->minscaledenom = $matches[1];
      else if ($label && preg_match('/^MAXSCALEDENOM ([0-9\.]+)$/i', $sz, $matches)) $this->maxscaledenom = $matches[1];
    }
  }

  /**
  * Convert `align` property to the text value or to the constant matching the text value.
  * @param string|integer $a
  * @return integer|string
  */
  private static function convertAlign($a = NULL) {
    $aligns = array(
      self::POSITION_UL     => 'UL',
      self::POSITION_LR     => 'LR',
      self::POSITION_UR     => 'UR',
      self::POSITION_LL     => 'LL',
      self::POSITION_CR     => 'CR',
      self::POSITION_CL     => 'CL',
      self::POSITION_UC     => 'UC',
      self::POSITION_LC     => 'LC',
      self::POSITION_CC     => 'CC',
      self::POSITION_XY     => 'XY',
      self::POSITION_AUTO   => 'AUTO',
      self::POSITION_AUTO2  => 'AUTO2',
      self::POSITION_FOLLOW => 'FOLLOW',
      self::POSITION_NONE   => 'NONE'
    );

    if (is_numeric($a)) return (isset($aligns[$a]) ? $aligns[$a] : FALSE);
    else return array_search($a, $aligns);
  }
  /**
  * Convert `position` property to the text value or to the constant matching the text value.
  * @param string|integer $p
  * @return integer|string
  */
  private static function convertPosition($p = NULL) {
    $positions = array(
      self::ALIGN_LEFT   => 'LEFT',
      self::ALIGN_CENTER => 'CENTER',
      self::ALIGN_RIGHT  => 'RIGHT'
    );

    if (is_numeric($p)) return (isset($positions[$p]) ? $positions[$p] : FALSE);
    else return array_search($p, $positions);
  }
  /**
  * Convert `size` property to the text value or to the constant matching the text value.
  * @param string|integer $s
  * @return integer|string
  */
  private static function convertSize($s = NULL) {
    $sizes = array(
      self::SIZE_TINY   => 'TINY',
      self::SIZE_SMALL  => 'SMALL',
      self::SIZE_MEDIUM => 'MEDIUM',
      self::SIZE_LARGE  => 'LARGE',
      self::SIZE_GIANT  => 'GIANT',
    );

    if (is_numeric($s)) return (isset($sizes[$s]) ? $sizes[$s] : FALSE);
    else return array_search($s, $sizes);
  }
  /**
  * Convert `type` property to the text value or to the constant matching the text value.
  * @param string|integer $t
  * @return integer|string
  */
  private static function convertType($t = NULL) {
    $types = array(
      self::TYPE_TRUETYPE => 'TRUETYPE',
      self::TYPE_BITMAP   => 'BITMAP'
    );

    if (is_numeric($t)) return (isset($types[$t]) ? $types[$t] : FALSE);
    else return array_search($t, $types);
  }
}