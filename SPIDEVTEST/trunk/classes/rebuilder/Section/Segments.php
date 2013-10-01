<?php
/**
 * Description of Segment
 * Root class (not abstract) as it maybe called directly during testing
 * but i should make it abstract, at the end
 *
 * @author MadTechie
 */
require_once 'nodes.php';
class Section_Segments extends nodes  {
  function createStyle($name, $value, $closeable=true){
    if(!$closeable || !empty($value)) $this->addStyle(new style($name, $value, $closeable));
  }
  function addSection($section){
    return $this->addNode($section);
  }
   
}