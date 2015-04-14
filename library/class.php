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

require_once('label.php');
require_once('style.php');

/**
 * MapFile Generator - LayerClass (CLASS) Class.
 * [MapFile CLASS clause](http://mapserver.org/mapfile/class.html).
 * @package MapFile
 * @author Jonathan Beliën <jbe@geo6.be>
 * @link http://mapserver.org/mapfile/class.html
 */
class LayerClass {
  /** @var \MapFile\Label[] List of labels. */
  private $_labels = array();
  /** @var \MapFile\Style[] List of styles. */
  private $_styles = array();

  /** @var string Defines which class a feature belongs to. */
  public $expression;
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
  /** @var string Name to use in legends for this class. */
  public $name;
  /** @var string Text to label features in this class with. */
  public $text;

  /**
  * Constructor.
  * @param string[] $class Array containing MapFile CLASS clause.
  * @todo Must read a MapFile CLASS clause without passing by an Array.
  */
  public function __construct($class = NULL) {
    if (!is_null($class)) $this->read($class);
  }

  /**
  * Return the list of the labels.
  * @return \MapFile\Label[]
  */
  public function getLabels() {
    return $this->_labels;
  }
  /**
  * Return the label matching the index sent as parameter.
  * @param integer $i Label Index.
  * @return \MapFile\Label|false false if the index is not found.
  */
  public function getLabel($i) {
    return (isset($this->_labels[$i]) ? $this->_labels[$i] : FALSE);
  }
  /**
  * Return the list of the styles.
  * @return \MapFile\Style[]
  */
  public function getStyles() {
    return $this->_styles;
  }
  /**
  * Return the style matching the index sent as parameter.
  * @param integer $i Style Index.
  * @return \MapFile\Style|false false if the index is not found.
  */
  public function getStyle($i) {
    return (isset($this->_styles[$i]) ? $this->_styles[$i] : FALSE);
  }

  /**
  * Add a new \MapFile\Label to the Class.
  * @param \MapFile\Label $label New Label.
  * @return \MapFile\Label New Label.
  */
  public function addLabel($label = NULL) {
    if (is_null($label)) $label = new Label();
    $count = array_push($this->_labels, $label);
    return $this->_labels[$count-1];
  }
  /**
  * Add a new \MapFile\Style to the Class.
  * @param \MapFile\Style $style New Style.
  * @return \MapFile\Style New Style.
  */
  public function addStyle($style = NULL) {
    if (is_null($style)) $style = new Style();
    $count = array_push($this->_styles, $style);
    return $this->_styles[$count-1];
  }

  /**
  * Write a valid MapFile CLASS clause.
  * @return string
  * @uses \MapFile\Label::write()
  * @uses \MapFile\Style::write()
  */
  public function write() {
    $class  = '    CLASS'.PHP_EOL;
    if (!empty($this->name)) $class .= '      NAME "'.$this->name.'"'.PHP_EOL;
    if (!empty($this->expression)) $class .= '      EXPRESSION "'.$this->expression.'"'.PHP_EOL;
    if (!is_null($this->minscaledenom)) $class .= '      MINSCALEDENOM '.floatval($this->minscaledenom).PHP_EOL;
    if (!is_null($this->maxscaledenom)) $class .= '      MAXSCALEDENOM '.floatval($this->maxscaledenom).PHP_EOL;
    if (!empty($this->text)) $class .= '      TEXT "'.$this->text.'"'.PHP_EOL;
    foreach ($this->_styles as $style) $class .= $style->write();
    foreach ($this->_labels as $label) $class .= $label->write(3);
    $class .= '    END # CLASS'.PHP_EOL;
    return $class;
  }

  /**
  * Read a valid MapFile CLASS clause (as array).
  * @param string[] $array MapFile CLASS clause splitted in an array.
  * @uses \MapFile\Label::read()
  * @uses \MapFile\Style::read()
  * @todo Must read a MapFile CLASS clause without passing by an Array.
  */
  private function read($array) {
    $class = FALSE; $class_label = FALSE; $class_style = FALSE;

    foreach ($array as $_sz) {
      $sz = trim($_sz);

      if (preg_match('/^CLASS$/i', $sz)) $class = TRUE;
      else if ($class && preg_match('/^END( # CLASS)?$/i', $sz)) $class = FALSE;

      else if ($class && preg_match('/^LABEL$/i', $sz)) { $class_label = TRUE; $label[] = $sz; }
      else if ($class && $class_label && preg_match('/^END( # LABEL)?$/i', $sz)) { $label[] = $sz; $this->addLabel(new Label($label)); $class_label = FALSE; unset($label); }
      else if ($class && $class_label) { $label[] = $sz; }

      else if ($class && preg_match('/^STYLE$/i', $sz)) { $class_style = TRUE; $style[] = $sz; }
      else if ($class && $class_style && preg_match('/^END( # STYLE)?$/i', $sz)) { $style[] = $sz; $this->addStyle(new Style($style)); $class_style = FALSE; unset($style); }
      else if ($class && $class_style) { $style[] = $sz; }

      else if ($class && preg_match('/^EXPRESSION "(.+)"$/i', $sz, $matches)) $this->expression = $matches[1];
      else if ($class && preg_match('/^MAXSCALEDENOM ([0-9\.]+)$/i', $sz, $matches)) $this->maxscaledenom = $matches[1];
      else if ($class && preg_match('/^MINSCALEDENOM ([0-9\.]+)$/i', $sz, $matches)) $this->minscaledenom = $matches[1];
      else if ($class && preg_match('/^NAME "(.+)"$/i', $sz, $matches)) $this->name = $matches[1];
      else if ($class && preg_match('/^TEXT "(.+)"$/i', $sz, $matches)) $this->text = $matches[1];
    }
  }
}
