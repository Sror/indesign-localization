<?php
/**
 * Description of Paragraphs
 *
 * @author MadTechie
 */
class Section_ParaStyle extends Section_Segments{
  public function __construct($value) {
    //$this->addStyle(new style('ParaStyle', $ParaStyle, false));
    $this->createStyle('ParaStyle', $value, false);
  }
  function addSection($section){
    #class_parents($this);
    #get_parent_class($this);
    
    return parent::addSection($section);
  }
}