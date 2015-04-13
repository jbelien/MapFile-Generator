<?php
namespace MapFile;

require_once('style.php');

class LayerClass {
  private $_labels = array();
  private $_styles = array();

  public $expression;
  public $maxscaledenom;
  public $minscaledenom;
  public $name;
  public $text;

  public function __construct($class = NULL) {
    if (!is_null($class)) $this->read($class);
  }

  public function getLabels() {
    return $this->_labels;
  }
  public function getLabel($i) {
    return (isset($this->_labels[$i]) ? $this->_labels[$i] : FALSE);
  }
  public function getStyles() {
    return $this->_styles;
  }
  public function getStyle($i) {
    return (isset($this->_styles[$i]) ? $this->_styles[$i] : FALSE);
  }

  public function addLabel($label = NULL) {
    if (is_null($label)) $label = new Label();
    $count = array_push($this->_labels, $label);
    return $this->_labels[$count-1];
  }
  public function addStyle($style = NULL) {
    if (is_null($style)) $style = new Style();
    $count = array_push($this->_styles, $style);
    return $this->_styles[$count-1];
  }

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
