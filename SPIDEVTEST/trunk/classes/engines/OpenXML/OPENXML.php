<?php
/**
 *
 */
class OpenXML {
  private $handler;
  private $sections = array();

  function __construct($filename) {
    require_once dirname ( __FILE__ ) . "/compile.php";

    $this->handler = new compiler ( );
    $this->handler->decompile ( $filename );
    $this->handler->debug = false;
    $this->deconstruct ();
  }
  private function deconstruct() {
    $files = $this->handler->getList ();
    if(!is_array($files)) return false;
    foreach ( array_keys ( $files ) as $file ) {
      $info = explode(".", $file);
      if($info[1] == "xml"){
	$this->sections[$file] = new XMLPart ( $this->handler, $file );
      }else{
	$this->sections[$file] = $this->handler->extract($file);
      }
    }
  }

  public function reBuild($filename) {
    $this->handler = new compiler ( );
    $this->handler->compile ( $filename, true );

    $this->handler = new compiler ( );
    $this->handler->compile ( $filename, true );
    foreach($this->sections as $K => $V){
      if($V instanceof XMLPart){
		$K = $V->getName();
		$value = $V->getValue();
		if(!$value instanceof SimpleXMLElement) continue;
		$V = $value->asXML();
      }
	  $this->handler->addData ( false, $K, '', $V );
    }
    $this->handler->save ();
    return $filename;
  }

  public function setSection($sect, $data){
    $tmp = new XMLPart($this->handler);
	$value = simplexml_load_string($data);
	if($value===false) return false;
    $tmp->setValue( $value );
    $tmp->setName( $sect );
    $this->sections[$sect] = $tmp;
	return true;
  }
  public function getSection($sect){
    if(!isset($this->sections[$sect])) return false;
    return $this->sections[$sect];
  }

  public function getListSections(){
    return array_keys($this->sections);
  }
}

class STRINGPart {
  protected $handler;
  private $name;
  private $data;
  function __construct(&$handler, $part = "") {
    $this->handler = $handler;
    if (! empty ( $part ))
      $this->loadValue ( $part );
  }
  function loadValue($part) {
    $this->name = $part;
    $this->data = $this->handler->extract ( $part );
  }
  function setValue($string) {
    $this->data = $string;
  }
  function __toString() {
    return $this->data;
  }
  function getName() {
    return $this->name;
  }
  function getValue() {
    return $this->data;
  }
}

class XMLPart {
  protected $handler;
  private $name;
  private $data;
  function __construct(&$handler, $part = "") {
    $this->handler = $handler;
    if (! empty ( $part ))
      $this->loadValue ( $part );
  }
  function loadValue($part) {
    $this->name = $part;
    $this->data = simplexml_load_string ( $this->handler->extract ( $part ) );
  }
  function setName($string) {
    $this->name = $string;
  }
  function setValue($string) {
    $this->data = $string;
  }
  function __toString() {
    return $this->data->asXML ();
  }
  function getName() {
    return $this->name;
  }
  function getValue() {
    return $this->data;
  }
}

require_once 'DocX_helper.php';