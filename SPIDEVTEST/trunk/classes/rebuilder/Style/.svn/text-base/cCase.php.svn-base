<?php
/**
 * Description of cCase
 *
 * @author MadTechie
 */
class Style_cCase extends Style_Segment {
  function  __construct($value) {
    parent::__construct('cCase',$value,true);
    //$this->keep = $this->validate($text);
  }

  function Validate(node $node){
    return (strlen($node->getText()) > 1);
  }
}