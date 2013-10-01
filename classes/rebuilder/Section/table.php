<?php
/**
 * Description of table
 *
 * @author MadTechie
 */
class Section_table extends Section_Segments{
  public function __construct($value) {
    #if(!empty($value)) $this->addStyle(new style('table', $value, true));
    $this->createStyle('table', $value, true);
  }
  /*function  __construct($value) {
    parent::__construct('table',$value,true);
    $this->setKeep(true);
  }*/

}