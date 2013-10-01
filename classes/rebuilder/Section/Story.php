<?php
/**
 * Description of Story
 * Root class (not abstract) as it maybe called directly during testing
 * but i should make it abstract, at the end
 *
 * @author MadTechie
 */
require_once 'nodes.php';
class Section_Story extends Section_Segments  {
  function addSection($section){
    //var_dump($section,$this);
    //$this->NodePop()
    $X = $this->LastNode();
    if(!$X){
      
    }
    return $this->addNode($section);
  }

  function LastNode(){
    $x = (count($this->nodes)-1);
    return ($x > 0)?$this->nodes[$x]:false;
  }
}