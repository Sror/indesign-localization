<?php
/**
 * TODO:
 * collapse styles (moved all styles in a single node)
 * Merge Text (if all styles are the same as the previous node append text to previoue node and remove current node),
 * Cleanup (remove unneeded styles - add this into collapse)
 * Get/Set Paragraphs (this should be a simple get text from nodes once its been cleaned up (see above) )
 * remove Paragraphs (*should* be a simple getText from node)
 *
 * FilterRules
 * ReOrder doesn't move tables (disbaled 663)
 *
 * Test
 * CLEAN UP EVERYTHING, this has tons of re-written code,
 * some of which will be used later, other will be dumped
 *
 */
/**
 * ProtoType Rebuilder Two
 *
 * @author MadTechie
 */
require_once 'nodes.php';
#require_once 'cSegments.php';
#require_once 'pSegments.php';
#require_once 'Paragraphs.php';

class paragraphObject {
  private $segment = array();

  public function addSegment(segmentObject $segment) {
    if (is_null($segment->getOrder()))
      $segment->setOrder(count($this->segment) + 1);
    if (is_null($segment->getNewOrder()))
      $segment->setNewOrder(count($this->segment) + 1);
    return array_push($this->segment, $segment);
  }

  public function getSegments() {
    return $this->segment;
  }
  public function getSegment($index) {
    if(!isset($this->segment[$index])) {
      trigger_error("No Segment found",E_USER_WARNING);
      return null;
    }
    return $this->segment[$index];
  }
  public function removeSegment($index) {
    if(!isset($this->segment[$index])) {
      trigger_error("No Segment found",E_USER_WARNING);
      return null;
    }
	$dom = $this->segment[$index]->getContentDOM()->getDOM();
	$dom->parentNode->removeChild($dom);
    unset($this->segment[$index]);
  }
  public function setSegment($index, segmentObject $segment) {
    if(!isset($this->segment[$index])) {
      trigger_error("No Segment found",E_USER_WARNING);
      return null;
    }
    return $this->segment[$index]=$segment;
  }

  public function __toString() {
    $str = '';
    foreach ($this->segment as $seg) {
      if(!$seg->getOmitted()) $str .= $seg;
    }
    return $str;
  }

}

abstract class stdDOM {

  protected $DOM;
  protected $NewOrder = null;
  protected $DomID;

  function __construct($DomID, DOMElement $DOM) {
    $this->DomID = $DomID;
    $this->DOM = $DOM;
  }

  public function getDOM() {
    return $this->DOM;
  }

  public function setDOM($DOM) {
    $this->DOM = $DOM;
  }

  public function getNewOrder() {
    return $this->NewOrder;
  }

  public function setNewOrder($NewOrder) {
    $this->NewOrder = $NewOrder;
  }

  public function getDomID() {
    return $this->DomID;
  }

  public function setDomID($DomID) {
    $this->DomID = $DomID;
  }

  function __clone() {
    $this->setDOM(clone $this->getDOM());
  }

}

class ContentDOM extends stdDOM {

}

class CharDOM extends stdDOM {

}

class ParaDOM extends stdDOM {

  function __construct(DOMElement $DOM) {
    $this->DomID = 0;
    $this->DOM = $DOM;
    $this->Order = 0; //count(CharDOMList::getDOMElements());
  }

}

class CharDOMList {

  protected static $DOMElement = array();

  public static function reset() {
    self::$DOMElement = array();
  }

  public static function getDOMElement($index) {
    if (!isset(self::$DOMElement[$index]))
      return null;
    return self::$DOMElement[$index];
  }

  static function addDOMElement(CharDOM $Element) {
    $key = array_search($Element, self::$DOMElement, true);
    if ($key === false) {
      if (is_null($Element->getNewOrder()))
	$Element->setNewOrder(count(self::$DOMElement) + 1);
      return array_push(self::$DOMElement, $Element) - 1;
    }else {
      return $key;
    }
  }
  static function removeDOMElement($index){
	if (!isset(self::$DOMElement[$index])) return false;
	unset(self::$DOMElement[$index]);
	return true;
  }

  public static function setDOMElements($DOMElement) {
    self::$DOMElement = $DOMElement;
  }

  public static function getDOMElements() {
    return self::$DOMElement;
  }

}

class ParaDOMList {

  protected static $DOMElement = array();

  public static function reset() {
    self::$DOMElement = array();
  }

  public static function getDOMElement($index) {
    if (!isset(self::$DOMElement[$index]))
      return null;
    return self::$DOMElement[$index];
  }

  static function addDOMElement(ParaDOM $Element) {
    $key = array_search($Element, self::$DOMElement, true);
    if ($key === false) {
      if (is_null($Element->getNewOrder()))
	$Element->setNewOrder(count(self::$DOMElement) + 1);
      return array_push(self::$DOMElement, $Element) - 1;
    }else {
      return $key;
    }
  }

  public static function setDOMElements($DOMElement) {
    self::$DOMElement = $DOMElement;
  }

  public static function getDOMElements() {
    return self::$DOMElement;
  }

}

class segmentObject {
  private $Order = null;
  private $NewOrder = null;
  private $Omitted = false;
  private $ContentDOM = null;
  private $hasText = false;
  private $Name='';
  
  function  __construct($CharDom=null) {
    if(!is_null($CharDom) && $CharDom instanceof ContentDOM) $this->setContentDOM($CharDom);
  }
  function __clone() {
    $this->setContentDOM(clone $this->getContentDOM());
  }

  function __toString() {
    return $this->getContent();
  }

  public function getContentDOM() {
    return $this->ContentDOM;
  }

  public function setContentDOM(ContentDOM $dom) {
    $this->ContentDOM = $dom;
  }

  public function getOrder() {
    return $this->Order;
  }

  public function setOrder($Order) {
    $this->Order = $Order;
  }

  public function getNewOrder() {
    return $this->NewOrder;
  }

  public function setNewOrder($NewOrder) {
    $this->NewOrder = $NewOrder;
  }

  public function HasText() {
    return (bool) $this->hasText;
  }

  public function setHasText($hasText) {
    $this->hasText = $hasText;
  }

  public function getContent() {
    if(!$this->HasText()) return "";
    return htmlspecialchars_decode($this->getNode()->nodeValue, ENT_QUOTES);
  }

  public function setContent($string) {
    return $this->getNode()->nodeValue = htmlspecialchars($string, ENT_QUOTES);
  }

  public function getNode() {
    $ContentDOM = $this->getContentDOM();
    return $ContentDOM->getDOM();
  }

  function getParent(){
    $ContentObj = $this->getContentDOM();
    $CharStyle = CharDOMList::getDOMElement($ContentObj->getDomID());
    //$CharacterStyle = $CharStyle->getDOM();
    return $CharStyle;
  }

  public function getOmitted() {
    return $this->Omitted;
  }

  public function setOmitted($Omitted) {
    $this->Omitted = $Omitted;
  }
  
  public function getName() {
    return $this->Name;
  }

  public function setName($Name) {
    $this->Name = $Name;
  }

  private function Array2Attr(array $array, DOMElement $DOMElement) {
    foreach ($array as $attrName => $attrNode) {
      $DOMElement->setAttribute($attrName, $attrNode);
    }
    return $DOMElement;
  }

  private function Attr2Array(DOMElement $DOMElement) {
    $array = array();
    if (!is_null($DOMElement->attributes)) {
      foreach ($DOMElement->attributes as $attrName => $attrNode) {
	$array[$attrName] = $attrNode->nodeValue;
      }
    }
    return $array;
  }

  private function Element2Array(DOMElement $DOMElement) {
    $Array = array();
    foreach ($DOMElement->childNodes as $ChildItem) {
      if (!$ChildItem instanceof DOMElement)
	continue;
      $Array[$ChildItem->tagName]['#'] = $ChildItem->nodeValue;
      $attr = $this->Attr2Array($ChildItem);
      if (count($attr))
	$Array[$ChildItem->tagName]['@'] = $attr;
    }
    return $Array;
  }

  private function ArrayRemoveElement(array $array, DOMElement $DOMElement) {
    foreach ($array as $tagName => $value) {
      if ($tagName == '@') {
	if (!is_array($value))
	  trigger_error('Expected Array', E_USER_ERROR);
	foreach ($value as $attr => $attrValue) {
	  $DOMElement->removeAttribute($attr);
	}
      } elseif ($tagName == '#') {
	if (is_array($value))
	  trigger_error('Expected String', E_USER_ERROR);
	$DOMElement->nodeValue = $value;
      }else {
	if (is_array($value)) {
	  $nodeList = $DOMElement->getElementsByTagName($tagName);
	  if ($nodeList->length == 0)
	    continue;
	  $node = $nodeList->item(0);
	  $node->parentNode->removeChild($node);
	  $element = $this->ArrayRemoveElement($value, $this->ContentDOM->createElement($tagName));
	}
      }
      //var_dump($attr,$this->dom->saveXML($element));
    }
    return $DOMElement;
  }

  private function Array2Element(array $array, DOMElement $DOMElement) {
    $this->ArrayRemoveElement($array, $DOMElement);
    foreach ($array as $tagName => $value) {
      if ($tagName == '@') {
	if (!is_array($value))
	  trigger_error('Expected Array', E_USER_ERROR);
	$DOMElement = $this->Array2Attr($value, $DOMElement);
      }elseif ($tagName == '#') {
	if (is_array($value))
	  trigger_error('Expected String', E_USER_ERROR);
	$DOMElement->nodeValue = $value;
      }else {
	if (is_array($value)) {
	  $element = $this->Array2Element($value, $this->ContentDOM->createElement($tagName));
	} else {
	  $element = $this->ContentDOM->createElement($tagName, $value);
	}
	$DOMElement->appendChild($element);
	#var_dump($this->dom->saveXML($DOMElement));
      }
    }
    return $DOMElement;
  }

  public function getHASH() {
    $CharacterStyle = $this->getDOMElement();
    $CharacterStyleProperties = $CharacterStyle->getElementsByTagName('Properties');
    if ($CharacterStyleProperties->length != 0) {
      $CharacterStylePropertie = $CharacterStyleProperties->item(0);

      $Style = $this->Attr2Array($CharacterStyle);

      $StyleHash = md5(serialize($Style));

      //if(isset($array['FillColor'])) unset($array['FillColor']); //Ignore Fill
      //$array['hello'] = "world";
      #$array = array("tester"=>"BoldFont");
      #$this->Array2Attr($array,$CharacterStyle);
      $StylePropertie = $this->Element2Array($CharacterStylePropertie);
      $StylePropertieHash = md5(serialize($StylePropertie));
      return md5("$StyleHash-$StylePropertieHash");

      //$ParagraphStyleHash = md5(serialize($Array));
      #echo "$ParagraphStyleHash-$CharacterHash\n";
      //$Array['@']['hello'] = 'world';
      //$Array['@'] = 'world'; //error
      //$Array['hello'] = 'world';
      //$Array['tester'] = array('@'=>array('myAttr'=>'attrValue'),'foo'=>"bar",'#'=>"TEXT");
      #$Array['AppliedFont'] = array('@'=>array('type'=>'string'),"test"=>array("@"=>array("foo"=>"bar")),'#'=>"MT Arial Unicode MS");
      //var_dump($Array);
      $this->Array2Element($Array, $CharacterStylePropertie);
      //*/
    }
  }

}

class rebuilder {

  protected $dom = null;
  static $xPath = null;
  protected $paras = array();
  private $filename = "";

  function __construct($storyfile) {
    CharDOMList::reset();
    ParaDOMList::reset();

    $this->dom = new DOMDocument();
    $this->setFilename($storyfile);
    $this->dom->load($storyfile);
    $this->dom->formatOutput = true;

    rebuilder::$xPath = new DOMXPath($this->dom);
  }
  function save($filename = null) {
	$this->reOrder();
    $filename = (!is_null($filename))?$filename:$this->getFilename();
    $this->dom->save($filename);
  }

  protected function setFilename($filename) {
    $this->filename = $filename;
  }
  protected function getFilename() {
    return $this->filename;
  }

  function getPara() {
    return $this->paras;
  }

  function setPara($paras) {
    $this->paras = $paras;
  }

  function extractPara() {
    ParaDOMList::reset();
    CharDOMList::reset();
    $this->paras = array();

    $Doc = $this->dom->getElementsByTagName('Document');
    if ($Doc->length == 0)return false;
    $stories = $Doc->item(0)->getElementsByTagName('Story');

    $oPara = new paragraphObject();
    $storys = $stories->item(0);
    foreach ($stories as $storys) {
      $this->extractParaStyles($storys,$oPara);
    }
    return $this->paras;
  }
  
  private function ResolveXMLElement(&$node){
    $xml = $node->getElementsByTagName('XMLElement');
    if($xml->length>0) $node = $xml->item(0);
    return $node;
  }

  private function extractParaStyles($story,$oPara,$Ref='') {
    //Check for XMLElement
    $this->ResolveXMLElement($story);
    
    #$ParagraphStyles = $story->getElementsByTagName('ParagraphStyleRange');
    $xPathQuery = 'ParagraphStyleRange';
    $ParagraphStyles = rebuilder::$xPath->query($xPathQuery, $story);

    foreach ($ParagraphStyles as $ParagraphStyle) {
      $ParaIndex = ParaDOMList::addDOMElement(new ParaDOM($ParagraphStyle));

      #$CharacterStyles = $ParagraphStyle->getElementsByTagName('CharacterStyleRange');
      $xPathQuery = 'CharacterStyleRange';
      $CharacterStyles = rebuilder::$xPath->query($xPathQuery, $ParagraphStyle);
      
      foreach ($CharacterStyles as $CharacterStyle) {
	$CharIndex = CharDOMList::addDOMElement(new CharDOM($ParaIndex, $CharacterStyle));

        $this->ResolveXMLElement($CharacterStyle);
	#$Contents = rebuilder::$xPath->query('Br|Content', $CharacterStyle);
	$xPathQuery = 'Content|Br|Table/Cell';
	$Contents = rebuilder::$xPath->query($xPathQuery, $CharacterStyle);
	if(is_null($Contents->item(0))) continue;
	$segments = array();
	foreach ($Contents as $Content) {
	  #echo $Content->tagName.":[".$Content->nodeValue."]\n";
	  switch (strtolower($Content->tagName)) {
	    case "cell":
	      // /*
	      //Append ParentNode
	      $CharDom = new ContentDOM($CharIndex, $Content->parentNode);
	      $seg = new segmentObject($CharDom);
	      $seg->setHasText(false);
	      #$seg->setContentDOM($CharDom);
	      #
	      $oPara->addSegment($seg);
	      #$this->paras[] = $oPara;
		$Name = ($Content->hasAttribute('Name'))?$Content->getAttribute('Name'):"";
		$seg->setName($Name);
	      //Extract Para
	      $this->extractParaStyles($Content,$oPara,$Name);
	      $oPara = new paragraphObject();
	      //*/
	      break;
	    case "br":
	    case "content":
	      $CharDom = new ContentDOM($CharIndex, $Content);
	      $seg = new segmentObject($CharDom);
		  $seg->setName($Ref);
	      #$seg->setContentDOM($CharDom);
	      if (strtolower($Content->tagName) == "content") {
		$oPara->addSegment($seg);
		$seg->setHasText(true);
	      }
	      if(strtolower($Content->tagName) == "br") {
		$oPara->addSegment($seg);
		$seg->setHasText(false);

		$this->paras[] = $oPara;
		$oPara = new paragraphObject();
	      }
	      break;
	  }
	}
      }
      $this->paras[] = $oPara; //add last
      $oPara = new paragraphObject();
    }
  }

  function MergeParas(&$paras) {
    foreach ($paras as $para) {
      $segs = $para->getSegments();
      $LastSegHash = null;
      echo "\nPARA\n";
      foreach ($segs as $seg) {
	$hash = $seg->getHASH();
	if ($LastSegHash != $hash) {
	  echo "\n-----------\n";
	  $LastSegHash = $hash;
	}
	$CharacterStyle = $seg->getDOMElement();
	#$xpath->query($query, $CharacterStyle);
	#$element = $this->dom->createElement('Br');
	#$CharacterStyle->appendChild($element);
	#$element = $this->dom->createElement('Content', 'More Text');
	#$CharacterStyle->appendChild($element);

	$seg->setDOMElement($CharacterStyle);
	#var_dump($this->dom->saveXML($CharacterStyle) );
      }
      #var_dump( $para->getContent() );
    }
  }

  function CharNewOrder() {
    $CharList = CharDOMList::getDOMElements();
    //*********************//
    //Sort Char Style
    $sortable = array();
    foreach ($CharList as $k => $CharDom) {
      $sortable[$CharDom->getDomID()][] = $CharDom;
    }
    $moves = array();
    foreach ($sortable as $parentID => $CharList) {
      #usort($CharList, array($this, "OrderByNewOrder"));
      foreach ($CharList as $CharDom) {
	$moves[$parentID][] = $CharDom;
      }
    }
    //Move CharStyles
    foreach ($moves as $parentID => $move) {
      $CharDom = $this->dom->createDocumentFragment();
      foreach ($move as $m) {
	$CharDom->appendChild($m->getDOM());
      }
      //Append
      $parent = ParaDOMList::getDOMElement($parentID)->getDOM();
      $parent->appendChild($CharDom);
    }
  }

  function ParaNewOrder() {
    $ParaList = ParaDOMList::getDOMElements();

    $parent = $ParaList[0]->getDOM()->parentNode;
    //Sort Para Style
    $sortable = array();
    foreach ($ParaList as $k => $ParaDom) {
      $sortable[$ParaDom->getDomID()][] = $ParaDom;
    }
    $moves = array();
    foreach ($sortable as $ParaList) {
      usort($ParaList, array($this, "OrderByNewOrder"));
    }
    //Move Para
    foreach ($moves as $move) {
      $ParaDom = $this->dom->createDocumentFragment();
      foreach ($move as $m) {
	$ParaDom->appendChild($m->getDOM());
      }
      //Append
      $parent->appendChild($ParaDom);
    }
  }

protected function cleanStory() {
		$CharList = CharDOMList::getDOMElements();
		foreach ($CharList as $index => $CharStyle) {
			$cdom = $CharStyle->getDOM();
			$data = trim($cdom->nodeValue);
			if (empty($data)) {
				#CharDOMList::removeDOMElement($index);
				#$cdom->parentNode->removeChild($cdom);
			}
		}
		$CharList = CharDOMList::getDOMElements();

		foreach ($this->getPara() as $p => $para) {
			foreach ($para->getSegments() as $s => $seg) {
				if ($seg->getOmitted()) {
					$para->removeSegment($s);
				}
			}
		}
	}

  function reOrder() {
	$this->cleanStory();
    /**
     * RANDOMISER
     */
    //Randomize CharStyles
    $CharDoms = CharDOMList::getDOMElements();
    foreach ($CharDoms as $i => $CharDom) {
      continue; //DISABLED
      $CharDom->setNewOrder(rand(1, 10000));
    }

    //Randomise Contents
    foreach ($this->paras as $p => $para) {
      continue; //DISABLED
      $segs = $para->getSegments();
      foreach ($segs as $n => $seg) {
	if ($seg->HasText()) {
	  #$seg->setNewOrder( rand(1, 999) );
	  $seg->setContent("[{$seg->getNewOrder()}]Paragraph:$p, Segment:$n, [".$seg->getContent()."]");
	  #echo $seg->getContent()."\n";
	} else {
	  #$seg->setNewOrder( rand(1, 999)+1000 );
	}
      }
    }

    //Sort Contents By Para and CharStyle
    $moves = array();
    foreach ($this->paras as $p => $para) {
      $segs = $para->getSegments();
      if (!empty($segs)) {
	//Create Sort Order (by CharStyle -> Para)
	$sortable = array();
	foreach ($segs as $seg) {
	  $cDom = $seg->getContentDOM()->getDOMID();
	  if (!isset($sortable[$cDom]))
	    $sortable[$cDom] = array();
	  $sortable[$cDom][] = $seg;
	}
	//Rebuild in order
	foreach ($sortable as $cDom => $segs) {
	  usort($segs, array($this, "OrderByNewOrder"));
	  if (!isset($moves[$cDom])) $moves[$cDom] = array();
	  foreach ($segs as $seg) {
	    $moves[$cDom][] = $seg->getContentDOM()->getDOM();
	  }
	}
      }
    }
    //Move contents
    foreach ($moves as $cDomID => $move) {
      $cDom = CharDOMList::getDOMElement($cDomID);
      if(is_null($cDom)) continue;
      $content = $this->dom->createDocumentFragment();
      foreach ($move as $m) {
	$content->appendChild($m);
      }
      
      //Append
      $cDom->getDOM()->appendChild($content);
    }
    $this->CharNewOrder();
    $this->ParaNewOrder();
  }

  protected function OrderByX($a, $b, $func) {
    $a_order = $a->$func();
    $b_order = $b->$func();
    return ($a_order == $b_order) ? 0 : ($a_order < $b_order) ? -1 : 1;
  }

  function OrderByOrder($a, $b) {
    return $this->OrderByX($a, $b, 'getOrder');
  }

  function OrderByNewOrder($a, $b) {
    return $this->OrderByX($a, $b, 'getNewOrder');
  }

  function OrderByRef($a, $b) {
    return $this->OrderByX($a, $b, 'getRef');
  }

  protected function getFile($name, $dir) {
    return file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . "$name.php");
    //Will probably update to below when extra options are needed
    if ($handle = opendir(dirname(__FILE__) . DIRECTORY_SEPARATOR . $dir)) {
      while (false !== ($file = readdir($handle))) {
	if ("$name.php" == $file) return true;
      }
    }
    return false;
  }

  function __toString() {
    $this->reOrder();
    return $this->dom->saveXML();
  }

}

class rebuilderControl extends rebuilder {
  function __construct($filein) {
    if(!file_exists($filein)) trigger_error("File doesn't exist [$filein]",E_USER_ERROR);
    $fileDetails = pathinfo($filein);
    $backupfile = dirname($filein).DIRECTORY_SEPARATOR.$fileDetails['filename'].".bak.".$fileDetails['extension'];

    if(file_exists($backupfile)) {
      if(!unlink($filein)) trigger_error("File can not be cleared",E_USER_ERROR);
      copy($backupfile,$filein);
    }else {
      copy($filein, $backupfile);
    }

    parent::__construct($filein);
  }

  public function cloneCharStyle($index){
	  // do something here
  }

  public function getParas() {
    if (!empty($this->para)) return $this->para;
    return $this->extractPara();
  }

  public function split($obj,$pos=.5){
    $obj_name = get_class($obj);
    switch($obj_name){
      case "segmentObject";
	$new = clone $obj;

	/*
	//POC
	$ContentObj = $new->getContentDOM();
	$ContentDOM = $ContentObj->getDOM();
	$CharStyle = CharDOMList::getDOMElement($ContentObj->getDomID());
	$CharacterStyle = $CharStyle->getDOM();
	//$CharacterStyle = $new->getParent();
	
	$ParaIndex = $CharStyle->getDomID();

	$newCharacterStyle = $CharacterStyle->cloneNode(false);
	$newContent = $ContentDOM->cloneNode(false);
	$newContent->nodeValue = '';
	$newCharacterStyle->appendChild($newContent);

	$newCharStyle = new CharDOM($ParaIndex, $newCharacterStyle);
	$newCharStyle->setNewOrder($CharStyle->getNewOrder()+$pos);
	$CharIndex = CharDOMList::addDOMElement($newCharStyle);
	$CharDom = new ContentDOM($CharIndex, $newContent);
	*/
	
	$ContentObj = $new->getContentDOM();
	$CharStyleIndex = $ContentObj->getDomID();
	
	$copiedCharStyleIndex = $this->copyCharDOM($CharStyleIndex);
	
	$curCharStyle = CharDOMList::getDOMElement($CharStyleIndex);
	$newCharStyle = CharDOMList::getDOMElement($copiedCharStyleIndex);
	$newCharStyle->setNewOrder($curCharStyle->getNewOrder()+$pos);
	
	$copiedContentObj = $this->copyCharContentToStyle($ContentObj,$copiedCharStyleIndex);
  	#var_dump($this->dom->saveXML( CharDOMList::getDOMElement($copiedCharStyleIndex)->getDOM()->parentNode  ));
  	#var_dump($this->dom->saveXML( CharDOMList::getDOMElement($copiedCharStyleIndex)->getDOM()  ));
	
	$seg = new segmentObject($copiedContentObj);
	$seg->setHasText(true);
	#$seg->setContent('123123132132');
	#die($this);
	#$this->ReIndexCharDOMList();
	return $seg;
      break;
      default:
	trigger_error("$obj_name is not splitable", E_USER_ERROR);
    }
  }
  
  public function copyCharDOM($CharStyleIndex){
    $CharStyle = CharDOMList::getDOMElement($CharStyleIndex);
    $CharacterStyle = $CharStyle->getDOM();
    $ParaIndex = $CharStyle->getDomID();
    $newCharacterStyle = $CharacterStyle->cloneNode(false);
    $CharacterStyle->parentNode->appendChild($newCharacterStyle);
    
    $newCharStyle = new CharDOM($ParaIndex, $newCharacterStyle);
    $CharStyleIndex = CharDOMList::addDOMElement($newCharStyle);
    return $CharStyleIndex;
  }
  
  public function copyCharContentToStyle(ContentDOM $ContentDOM, $CharStyleIndex){
    $ContentDOM = $ContentDOM->getDOM();    
    $newContent = $ContentDOM->cloneNode(false);
    $newContent->nodeValue = '';

    $CharStyle = CharDOMList::getDOMElement($CharStyleIndex);
    $CharacterStyle = $CharStyle->getDOM();
    $CharacterStyle->appendChild($newContent);
    return new ContentDOM($CharStyleIndex, $newContent);
  }

  public function ReIndexCharDOMList() {
    //Reset Assoc
    $a = CharDOMList::getDOMElements();
    usort($a, array($this, "OrderByNewOrder"));
    CharDOMList::reset();
    foreach ($a as $k => $e) {
      $e->setNewOrder(null);
      CharDOMList::addDOMElement($e);
    }
  }

  public function ReIndexParaDOMList() {
    //Reset Assoc
    $a = ParaDOMList::getDOMElements();
    usort($a, array($this, "OrderByNewOrder"));
    ParaDOMList::reset();
    foreach ($a as $k => $e) {
      $e->setNewOrder(null);
      ParaDOMList::addDOMElement($e);
    }
  }

}
