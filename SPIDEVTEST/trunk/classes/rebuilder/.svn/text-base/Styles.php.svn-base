<?php
/**
 * Description of styles
 *
 * @author MadTechie
 */
require_once 'Style.php';
class Styles {
  protected $Styles = array();

  function  __construct() {
    require_once 'loader.php';
  }

  public function addStyle(style $style) {
    array_push($this->Styles, $style);
  }

  public function removeStyle($style) {
    if(isset($this->Styles[$style])) unset($this->Styles[$style]);
  }

  public function getStyles() {
    return $this->Styles;
  }

  public function getStylesHash($node) {
    $styHash = array();
    foreach($this->Styles as $style){
      if($style instanceof StyleSegment) {
	if(!$style->Validate($node)) continue;
	$styHash[] = $style;
      }
    }
    return md5(serialize($styHash));
  }

  public function RemoveAllStyles($except = array()) {
    $Styles = array_keys($this->getStyles());
    foreach ($Styles as $Style) {
      if (!in_array($Style, $except)) {
	$this->removeStyle($Style);
      }
    }
  }

  public function RemoveStyles($removals = array()) {
    $Styles = array_keys($this->getStyles());
    foreach ($Styles as $Style) {
      if (in_array($Style, $removals)) {
	$this->removeStyle($Style);
      }
    }
  }

  function __toString() {
        $str = $this->getText();
	return $str;
        $styles = $this->getStyles();
        foreach ($styles as $style) {
            $Key = $style->getKey();
            $Value = $style->getValue();
            $Closable = $style->getClosable();
            if ($style instanceof Style_Segment) {
                if (!$style->Validate($this)) continue;
            }
            if ($Closable) {
                $str = "<$Key:$Value>$str<$Key:>";
            } else {
                $str = "<$Key:$Value>$str";
            }
	    echo "~$str~;";
        }
	echo "#\n";
        return $str;
    }

    public function toString($str){
	//$str = $this->getText();
        $styles = $this->getStyles();
        foreach ($styles as $style) {
            $Key = $style->getKey();
            $Value = $style->getValue();
            $Closable = $style->getClosable();
            if ($style instanceof Style_Segment) {
                if (!$style->Validate($this)) continue;
            }
            if ($Closable) {
                $str = "<$Key:$Value>$str<$Key:>";
            } else {
                $str = "<$Key:$Value>$str";
            }
        }
        return $str;
    }
}