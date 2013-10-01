<?php
/**
 * Description of ParaStyle
 *
 * @author MadTechie
 */
class Style_ParaStyle extends Style_Segment {
  function  __construct($value) {
    parent::__construct('ParaStyle',$value,false);
    //$this->keep = $this->validate($text);
  }

}