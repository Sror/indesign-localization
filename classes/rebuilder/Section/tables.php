<?php
//NEED REWORKING
/**
 * Description of tables
 *
 * @author MadTechie
 */

class inddTable {
  private $TableStyle;
  private $settings = array();

  function __construct() {
    $this->settings['tCellDefaultCellType'] = 'Text';
  }

  public function __clone() {
    foreach ($this as $key => $val) {
      if (is_object($val) || (is_array($val))) {
	$this->{$key} = unserialize(serialize($val));
      }
    }
  }

  function addSetting($setting, $value) {
    $this->settings[$setting] = $value;
  }

  function setTableStyle($TableStyle) {
    $this->TableStyle = $TableStyle;
  }

  function setDefaultCellType($DefaultCellType) {
    $this->settings['tCellDefaultCellType'] = $DefaultCellType;
  }

  private $Cols = array();

  function addCol(inddTableCol $col) {
    $this->Cols[] = $col;
  }

  private $Rows = array();

  function addRow(inddTableRow $Row) {
    $this->Rows[] = $Row;
  }
  function getRows() {
    return $this->Rows;
  }

  function __toString() {
    //Finish Above
    $Style = '';
    //$Style .= sprintf('<tRowAttrHeight:%f>',$this->height);
    foreach ($this->settings as $K => $V) {
      $Style .= sprintf('<%s:%s>', $K, $V);
    }
    $ret = sprintf("<TableStyle:%s><TableStart:%d,%d:0:0%s>%s%s<TableEnd:>",
	    $this->TableStyle,
	    count($this->Rows),
	    count($this->Cols),
	    $Style,
	    implode('', $this->Cols),
	    implode('', $this->Rows)
    );
    return $ret;
  }

}

class inddTableCol {

  private $settings = array();

  function __construct($width) {
    $this->setWidth($width);
  }

  function setWidth($width) {
    $this->settings['tColAttrWidth'] = $width;
  }

  function addSetting($setting, $value) {
    $this->settings[$setting] = $value;
  }

  function __toString() {
    $Style = '';
    foreach ($this->settings as $K => $V) {
      $Style .= sprintf('<%s:%s>', $K, $V);
    }
    return "<ColStart:$Style>";
  }

}

class inddTableRow {

  private $cells = array();
  #private $height = 0;
  private $settings = array();

  public function __clone() {
    foreach ($this as $key => $val) {
      if (is_object($val) || (is_array($val))) {
	$this->{$key} = unserialize(serialize($val));
      }
    }
  }

  function addSetting($setting, $value) {
    $this->settings[$setting] = $value;
  }

  #function setHeight($height){
  #  $this->height = $height;
  #}

  function addCell(inddTableCell $cell) {
    $this->cells[] = $cell;
  }

  function getCells() {
    return $this->cells;
  }

  function __toString() {
    $ret = "";
    $Style = '';
    //$Style .= sprintf('<tRowAttrHeight:%f>',$this->height);
    foreach ($this->settings as $K => $V) {
      $Style .= sprintf('<%s:%s>', $K, $V);
    }

    $ret .= "<RowStart:$Style>";
    foreach ($this->cells as $cell) {
      $ret .= (string) $cell;
    }
    $ret .= "<RowEnd:>";
    return $ret;
  }

}

require_once 'node.php';
class inddTableCell extends nodes {

  private $Paras = array();
  private $CellStyle = null;
  private $StylePriority = null;
  private $CellStart = null;
  private $settings = array();

  function addSetting($setting, $value) {
    $this->settings[$setting] = $value;
  }

  function addPara(Paragraph &$para) {
    $this->Paras[] = $para;
  }

  function getParas() {
    return $this->Paras;
  }

  function setCellStyle($CellStyle) {
    $this->CellStyle = $CellStyle;
  }

  function setStylePriority($StylePriority) {
    $this->StylePriority = $StylePriority;
  }

  function setCellStart($CellStart1, $CellStart2) {
    $this->CellStart = "$CellStart1,$CellStart2";
  }

  function __toString() {
    $ret = "";
    $ret .= "<CellStyle:{$this->CellStyle}>";
    $ret .= "<StylePriority:{$this->StylePriority}>";
    $Style = '';
    foreach ($this->settings as $K => $V) {
      $Style .= sprintf('<%s:%s>', $K, $V);
    }

    $ret .= "<CellStart:{$this->CellStart}$Style>";
    foreach ($this->Paras as $para) {
      $ret .= (string) $para;
    }
    $ret .= "<CellEnd:>";
    return $ret;
  }

}
