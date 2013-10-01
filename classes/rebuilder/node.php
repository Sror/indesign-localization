<?php
/**
 * Description of node
 *
 * @author MadTechie
 */
require_once 'Styles.php';
class node extends Styles {
  private $text = null;
  function  __construct($text=null) {
    $this->text = $text;
    parent::__construct();
  }

  function hasText() {
    return !is_null($this->text);
  }
  function getText() {
    return $this->text;
  }
  function setText($text) {
    $this->text = $text;
  }
  function appendText($text) {
    $this->text .= $text;
  }

  function isEmpty(){
    if(count($this->Styles)>0) return false;
    if($this->hasText()) return false;
    return true;
  }

}