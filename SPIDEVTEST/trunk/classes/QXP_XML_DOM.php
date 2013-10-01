<?php
/*
 * Extend DOMDocument
 */
class QXPDOMDocument extends DOMDocument {
	function createElement($name, $value = null) {
		$orphan = new QXPDOMElement($name, $value); // new  sub-class object
		$docFragment = $this->createDocumentFragment(); // lightweight container maintains "ownerDocument"
		$docFragment->appendChild($orphan); // attach
		$ret = $docFragment->removeChild($orphan); // remove
		return $ret;
	}
}

class QXPDOMElement extends DOMElement {
	function  __construct($name, $value='', $uri=null) {
		parent::__construct($name, $value, $uri);
	}
	function setAttribute($name, $value){
		if(!empty($value) || is_numeric($value)) {
			parent::setAttribute($name,$value);
		}
	}
}