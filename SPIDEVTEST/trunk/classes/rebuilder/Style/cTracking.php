<?php
/**
 * Description of cTracking
 *
 * @author MadTechie
 */
class Style_cTracking extends Style_Segment {
  function  __construct($value) {
    parent::__construct('cTracking',$value,true);
    $this->keep = true;
  }
}