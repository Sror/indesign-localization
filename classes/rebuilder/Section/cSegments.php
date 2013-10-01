<?php
/**
 * Description of cSegment
 *
 * @author MadTechie
 */
class SegConst {
  const SegPrefix=1;
  const SegAppend=2;
}

class Section_cSegments extends Section_Segments {

  #function addParagraph($Style, $text){
  function addParagraph($Style){
    $this->addStyle(new Style('ParaStyle',$Style,false));
    #$node = new node($text);
    #$this->addNode($node);
  }
  /*
  //overriden MUST HAVE
  function __toString() {
    $str = implode('', $this->getNodes());
    $style = $this->getStyles();
    krsort($style);
    if (isset($style['cNextXChars'])) {
      $cNextXChars = $style['cNextXChars'];
      unset($style['cNextXChars']);
      $style['cNextXChars'] = $cNextXChars;
    }
    if (isset($style['CharStyle'])) {
      $CharStyle = $style['CharStyle'];
      unset($style['CharStyle']);
      $style['CharStyle'] = $CharStyle;
    }
    foreach ($style as $K => $V) {
      if ($K == "cNextXChars") {
	$str = "<$K:$V>$str";
      } elseif ($K == "CharStyle") {
	if (!empty($V)) {
	  $str = "<$K:$V>$str";
	} else {
	  $str = "$str<$K:>";
	}
      } else {
	$str = "<$K:$V>$str<$K:>";
      }
    }
    if (empty($str))
      return "";
    return $str;
  }*/
  public function addNode(node $node, $pos=SegConst::SegAppend) {
    if (!is_array($this->nodes)) $this->nodes = array();
    switch ($pos) {
      case SegConst::SegAppend:
	array_push($this->nodes, $node);
	break;
      case SegConst::SegPrefix:
	array_unshift($this->nodes, $node);
	break;
      default:
	$callee = debug_backtrace();
	$callee = $callee[count($callee) - 2];
	$debug = "<br /> see file {$callee['file']}, line {$callee['line']}";
	trigger_error("Error: invalid Position for adding text$debug", E_USER_ERROR);
	break;
    }
  }
}
