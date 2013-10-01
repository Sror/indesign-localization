<?php
/**
 * Description of Style
 *
 * @author MadTechie
 */
class Style {
    private $Key='';
    private $Value='';
    private $Closable=true;
    function  __construct($Key,$Value,$Closable=true) {
      $this->setKey($Key);
      $this->setValue($Value);
      $this->setClosable($Closable);
    }
    
    public function getKey() {
      return $this->Key;
    }

    public function setKey($Key) {
      $this->Key = $Key;
    }

    public function getValue() {
      return $this->Value;
    }

    public function setValue($Value) {
      $this->Value = $Value;
    }

    public function getClosable() {
      return $this->Closable;
    }

    public function setClosable($Closable) {
      $this->Closable = $Closable;
    }

}