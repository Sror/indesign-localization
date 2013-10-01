<?php
/**
 * INDD_Story_Rebuilder Class
 * @version 1.00 r12 WTF!
 * @author Richard Thompson
 */
class INDD_text_Rebuilder{
	private $text = '';
	private $ColorTable = '';
	//Details
	private $FileFormat = '';
	private $FileVersion = '';
	private $FeatureSet = '';

	private $paragraphs = array();
	private $tables = array();
	private $Items = array();

	private $DefineCharStyle = array();
	private $DefineListStyle = array();
	private $DefineParaStyle = array();
	private $DefineCompositeFont = array();
	private $DefineKinsokuStyle = array();
	private $DefineMojikumiStyle = array();

	private $Removals = array();
	private $Keeps = array();

	protected $current = -1;

	public function setRemovals($Removals){
		$this->Removals = $Removals;
	}
	public function setKeeps($Keeps){
		$this->Keeps = $Keeps;
	}
	public function  __construct() {

	}
	function removeUnwanted(){
	  $this->text = preg_replace('/<(?:HyperlinkDestDefn|Hyperlink:=<).*?>>/um', '', $this->text);
	}
	public function Parsa($text){
		$this->masterReset();
		$this->text = $text;
		$this->removeHeaders();
		$this->removeDefined();
		$this->removeUnwanted();
		$injected = $this->Injest();
		while($Para = $this->NextItem()){
			$Para->Merge();
			while($pSeg = $Para->NextItem()){
				while($cSeg = $pSeg->NextItem()){
					//Remove all Except
					$cSeg->RemoveAllStyles($this->Keeps);
					//Remove selected styles
					#$pSeg->RemoveStyles($this->Removals);
					$pSeg->setCurrent($cSeg);
				}
				$pSeg->Merge();
				$Para->setCurrent($pSeg);
			}
			$this->setCurrent($Para);
		}
		return $injected;
	}
	protected function FindAndRemove($RegEx, &$Variable){
		if (preg_match($RegEx, $this->text, $regs)) {
			$Variable = $regs[0];
			$this->text = preg_replace($RegEx, '', $this->text);
		}
		return $Variable;
	}
	protected function FindAndRemoveWithOptions($RegEx, &$Variable, $Head) {
		if (preg_match($RegEx, $this->text, $regs)) {
			$Values = $regs[1];
			$NewValues = "<$Head";
			$DefineReg = '/^:([^=]*)=(.*$)/i'; //Maybe * instead of + (unsure why + was used)
			if (preg_match($DefineReg, $Values, $match)) {
				$prime = $match[1];
				$options = $match[2];
				$NewValues .= ":$prime=";
				$OptionsStr = "";
				preg_match_all('/<([^:>]+):([^>]+)>/i', $options, $result, PREG_SET_ORDER);
				foreach ($result as $V) {
					if (!empty($V[2]) || is_numeric($V[2]))
						$OptionsStr .="<{$V[1]}:{$V[2]}>";
				}
				$NewValues .= $OptionsStr;
			}
			if(is_array($Variable)){
				$Variable[] = $NewValues . ">\r\n";
			}else{
				$Variable = $NewValues . ">\r\n";
			}
			$this->text = preg_replace($RegEx, '', $this->text);
			return true;
		}else{
			return false;
		}
	}
	protected function removeHeaders() {
		$this->removeFileFormat();
		$this->removeFeatureSet();
		$this->removeFileVersion();
	}
	protected function removeFeatureSet(){
		$FeatureSetReg = '/<FeatureSet:([^>]*)>/ui';
		return $this->FindAndRemove($FeatureSetReg, $this->FeatureSet);
	}
	protected function removeFileVersion(){
		$FileVersionReg = '/<Version:(\d+)>/ui';
		return $this->FindAndRemove($FileVersionReg, $this->FileVersion);
	}
	protected function removeFileFormat(){
		$FileFormatReg = '/^<([^>]+)>\r\n/ui';
		return $this->FindAndRemove($FileFormatReg, $this->FileFormat);
	}

	protected function removeDefined(){
		$this->removeColorTable();
		$this->removeDefineKinsokuStyle();
		$this->removeDefineMojikumiStyle();
		$this->removeDefineCharStyle();
		$this->removeDefineListStyle();
		$this->removeDefineCompositeFont();
		$this->removeDefineParaStyle();
		$this->text = trim($this->text);
	}

	protected function removeColorTable() {
		$ColorTableReg = '/^<ColorTable(.*?[^\\\\](?:>)?)>\r\n/ui';
		//return $this->FindAndRemove($ColorTableReg, $this->ColorTable);
		return $this->FindAndRemoveWithOptions($ColorTableReg, $this->ColorTable, 'ColorTable');
	}

	protected function removeDefineCharStyle() {
		$DefineCharStyleReg = '/^<DefineCharStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
		//return $this->FindAndRemove($DefineCharStyleReg, $this->DefineCharStyle[]);
		while($this->FindAndRemoveWithOptions($DefineCharStyleReg, $this->DefineCharStyle, 'DefineCharStyle'));
	}

	protected function removeDefineKinsokuStyle() {
		$DefineKinsokuStyleReg = '/^<DefineKinsokuStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
		//return $this->FindAndRemove($DefineKinsokuStyleReg, $this->DefineKinsokuStyle[]);
		while($this->FindAndRemoveWithOptions($DefineKinsokuStyleReg, $this->DefineKinsokuStyle, 'DefineKinsokuStyle'));
	}

	protected function removeDefineMojikumiStyle() {
		$DefineMojikumiStyleReg = '/^<DefineMojikumiStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
		//return $this->FindAndRemove($DefineMojikumiStyleReg, $this->DefineMojikumiStyle[]);
		while($this->FindAndRemoveWithOptions($DefineMojikumiStyleReg, $this->DefineMojikumiStyle, 'DefineMojikumiStyle'));
	}

	protected function removeDefineCompositeFont() {
		$DefineCompositeFontReg = '/^<DefineCompositeFont(.*?[^\\\\](?:>)?)>\r\n/ui';
		//return $this->FindAndRemove($DefineCompositeFontReg, $this->DefineCompositeFont[]);
		while($this->FindAndRemoveWithOptions($DefineCompositeFontReg, $this->DefineCompositeFont, 'DefineCompositeFont'));
	}

	protected function removeDefineParaStyle() {
		$DefineParaStyleReg = '/^<DefineParaStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
		//return $this->FindAndRemove($DefineParaStyleReg, $this->DefineParaStyle[]);
		while($this->FindAndRemoveWithOptions($DefineParaStyleReg, $this->DefineParaStyle, 'DefineParaStyle'));
	}

	protected function removeDefineListStyle() {
		$DefineListStyleReg = '/^<DefineListStyle(.*?[^\\\\](?:>)?)>\r\n/ui';
		//return $this->FindAndRemove($DefineListStyleReg, $this->DefineListStyle[]);
		while($this->FindAndRemoveWithOptions($DefineListStyleReg, $this->DefineListStyle, 'DefineListStyle'));
	}

	public function addItem($Item,$id) {
	  $this->Items[] = array($Item,$id);
	}
	public function addParagraph(Paragraph $Paragraph) {
	  return array_push($this->paragraphs, $Paragraph)-1;
	}
	public function addTable(inddTable $table) {
	  return array_push($this->tables, $table)-1;
	}
	public function replaceParagraph($Key, Paragraph $Paragraph){
		$this->paragraphs[$Key] = $Paragraph;
	}
	protected function Injest() {
		$opencSegments = array();
		$openpSegments = array();
		$paragraph = null;
		$p_segment = null;
		$c_segment = null;
		$SkimReg = '/^<([^:]*):([^>]*)>/us';
		$tableKey = 0;
		$TableOpen = array($tableKey=>false);
		$lastCell = array($tableKey=>false);
		$TableContrainer = array($tableKey=>array());
		while (!empty($this->text)) {
			if (preg_match($SkimReg, $this->text, $regs)) {
				$Style = $regs[1];
				$Value = $regs[2];
				$Cleaned = false;
				if ($Style == 'ParaStyle' || $Style == 'pstyle') {
					if (!is_null($paragraph)) {
						if (!is_null($p_segment)) {
							if (!is_null($c_segment)) {
							  $p_segment->addSegment($c_segment);
							}
							$paragraph->addSegment($p_segment);
						}
						if ($TableOpen[$tableKey] ){
							$cell->addPara($this->paragraphs[$this->addParagraph($paragraph)]);
						}else{
							if(!$lastCell[$tableKey]) $this->addItem('Para', $this->addParagraph($paragraph));
						}
						if($lastCell[$tableKey]) $lastCell[$tableKey]= false;
					}
					$paragraph = new Paragraph($Value);
					$p_segment = null;
					$c_segment = null;
					//ParaStyles in Tables don't seam to follow the exact same logic :(
				} elseif ($Style == 'TableStyle') {
					if (!is_null($paragraph)) {
					  if (!is_null($p_segment)) {
					    if (!is_null($c_segment)) {
					      $p_segment->addSegment($c_segment);
					    }
					    $paragraph->addSegment($p_segment);
					  }
					  //$this->addItem('Para', $this->addParagraph($paragraph));
					  $TableContrainer[$tableKey][0] = clone $paragraph;
					  #$TableContrainer[$tableKey][1] = clone $p_segment;
					  #$TableContrainer[$tableKey][2] = clone $c_segment;
					  $TableContrainer[$tableKey][3] = $openpSegments;
					  $TableContrainer[$tableKey][4] = $opencSegments;

						$TableContrainer[$tableKey][5] = null;
						$TableContrainer[$tableKey][6] = null;
						if(!empty($cell)) $TableContrainer[$tableKey][5] = clone $cell;
						if(!empty($row)) $TableContrainer[$tableKey][6] = clone $row;

					  $paragraph = null;
					  $p_segment = null;
					  $c_segment = null;
					  $openpSegments = null;
					  $opencSegments = null;
					}
					$this->text = preg_replace($SkimReg, '', $this->text, 1);
					$Cleaned = true;
					//NEED TO REFACTOR
					$TableReg = '/^<TableStart:(\d+),(\d+):(\d+):(?:>|(.*?>)>)/i';
					if (preg_match($TableReg, $this->text, $matches)) {
						$paragraph = null;
						$p_segment = null;
						$c_segment = null;
						$tableKey++;
						$TableOpen[$tableKey] = true;
						unset($matches[0]);
						//Create Table
						$table = new inddTable();
						$table->setTableStyle($Value);
						preg_match_all('/<([^:>]+):([^>]+)>/i', $matches[4], $result, PREG_SET_ORDER);
						foreach($result as $V){
						  $table->addSetting($V[1], $V[2]);
						}
						//Clear TableStart
						$this->text = preg_replace($TableReg, '', $this->text, 1);

						//AddCol's
						//NEED to refactor
						$tColReg = '/^<ColStart:(?:>|(.*?>)>)/i';
						while (preg_match($tColReg, $this->text, $result)) {
							$col = new inddTableCol(0);
							preg_match_all('/<([^:>]+):([^>]+)>/i', $result[1], $result, PREG_SET_ORDER);
							foreach($result as $V){
							  $col->addSetting($V[1], $V[2]);
							}
							$table->addCol($col);
							$this->text = preg_replace($tColReg, '', $this->text, 1);
						}
						$Cleaned = true;
					}
				} elseif ($Style == 'TableEnd') {
					$TableOpen[$tableKey] = false;
					$this->addItem('Table', $this->addTable($table));
					$tableKey--;
					$lastCell[$tableKey] = true;
					if(count($TableContrainer[$tableKey])){
					  $paragraph = clone $TableContrainer[$tableKey][0];
					  #$p_segment = $TableContrainer[$tableKey][1];
					  #$c_segment = $TableContrainer[$tableKey][2];
					  $openpSegments = $TableContrainer[$tableKey][3];
					  $opencSegments = $TableContrainer[$tableKey][4];
					  $cell = null;
					  $row = null;
					  if(!empty($TableContrainer[$tableKey][5])) $cell = clone $TableContrainer[$tableKey][5];
					  if(!empty($TableContrainer[($tableKey)][6])) $row = clone $TableContrainer[$tableKey][6];
					  $TableContrainer[$tableKey] = array();
					}
					$this->text = preg_replace('/^<TableEnd:>[\r\n]*/sim', '', $this->text);
					#$paragraph = null;
					#$openpSegments = array();
					#$opencSegments = array();
					$Cleaned = true;
				} elseif ($Style == 'RowStart' || $Style == 'RowEnd') {
					//AddRow's
					if ($Style == 'RowStart') {//Open
						//need to refactor tRowAttrMinRowSize
						$tRowReg = '/^<RowStart:(?:>|(.*?>)>)/i';
						if (preg_match($tRowReg, $this->text, $result)) {
							$row = new inddTableRow();
							preg_match_all('/<([^:>]+):([^>]+)>/i', $result[1], $result, PREG_SET_ORDER);
							foreach($result as $V){
							  $row->addSetting($V[1], $V[2]);
							}
							$this->text = preg_replace($tRowReg, '', $this->text, 1);
							$Cleaned = true;
						}
					} elseif ($Style == 'RowEnd') {//Close
						$table->addRow($row);
						$row = null;
					}
				} elseif ($Style == 'StylePriority') {
					$CellStylePriority = $Value;
				} elseif ($Style == 'CellStyle') {
					$CellStyle = $Value;
				} elseif ($Style == 'CellStart' || $Style == 'CellEnd') {
					if ($Style == 'CellStart') {
						$cell = new inddTableCell();
						//list($s, $e) = explode(',', $Value);
						//$cell->setCellStart((int)$s, (int)$e);
						if (preg_match('/^(\d+),(\d+)/i', $Value, $regs)) {
						  $cell->setCellStart((int)$regs[1], (int)$regs[2]);
						}
						$tCellReg = '/^<CellStart:\d+,\d+(?:>|(.*?>)>)/i';
						if (preg_match($tCellReg, $this->text, $resultall)) {
							if(!empty($resultall[1])){
							  preg_match_all('/<([^:>]+):([^>]+)>/i', $resultall[1], $result, PREG_SET_ORDER);
							  foreach($result as $V){
							    $cell->addSetting($V[1], $V[2]);
							  }
							}
							$this->text = preg_replace($tCellReg, '', $this->text, 1);
							$Cleaned = true;
						}
						$cell->setStylePriority($CellStylePriority);
						$cell->setCellStyle($CellStyle);
					} elseif ($Style == 'CellEnd') {
						if (!is_null($paragraph)) {
							if (!is_null($p_segment)) {
								if (!is_null($c_segment)) {
									$p_segment->addSegment($c_segment);
								}
								$paragraph->addSegment($p_segment);
							}
							//Fails on table in a table
							$cell->addPara($this->paragraphs[$this->addParagraph($paragraph)]);
						}
						$paragraph = null;
						$p_segment = null;
						$c_segment = null;
						$row->addCell($cell);
						$cell = null;
					}
				} else {
					$Type = substr($Style, 0, 1);
					$Type2 = substr($Style, 0, 2);
					if ($Type == 'p' || $Type2 == 'bn') {
						if (!empty($Value) || is_numeric($Value)) {
							if (is_null($p_segment)) {
								$p_segment = new pSegments();
								$c_segment = null;
							}
							$openpSegments[$Style] = $Value;
						} else {
							if(isset($openpSegments[$Style])){
								if (is_null($p_segment)) {
									$p_segment = new pSegments();
									$c_segment = null;
								}
								$p_segment->addStyle($Style, $openpSegments[$Style]);
								unset($openpSegments[$Style]);
							}else{
								//Wasn't opened!
							}
						}
						if (count($openpSegments) == 0 && !is_null($p_segment)) {
							if (!is_null($c_segment)) {
								$p_segment->addSegment($c_segment);
								$c_segment = null;
							}
							if(!$paragraph instanceof Paragraph){
							  #var_dump($this->text);
							  return false;
							}else{
							  $paragraph->addSegment($p_segment);
							  $p_segment = null;
							}
						}
					} elseif ($Type == 'c' || $Style == "CharStyle") {
						if (is_null($c_segment)) {
							if (is_null($p_segment)) {
								$p_segment = new pSegments();
							}
							$c_segment = new cSegments();
						}
						if (!empty($Value) || is_numeric($Value)) {
							if ($c_segment->getText() != "") {
								$p_segment->addSegment($c_segment);
								$c_segment = new cSegments();
							}
							if ($Style == "cNextXChars" || $Style == "CharStyle") {
								$c_segment->addStyle($Style, $Value);
							} else {
								$opencSegments[$Style] = $Value;
							}
						} else {
							$c_segment->addStyle($Style, $opencSegments[$Style]);
							unset($opencSegments[$Style]);
						}
					}
					if (count($opencSegments) == 0 && !is_null($c_segment)) {
						$p_segment->addSegment($c_segment);
						$c_segment = null;
					}
				}
				if (!$Cleaned)
					$this->text = preg_replace($SkimReg, '', $this->text, 1);
			}elseif (preg_match('/(^[^<]*)/usm', $this->text, $regs)) {
				if (!$paragraph instanceof Paragraph){
					var_dump($this->text);die;
					return false;
				}
				if (is_null($c_segment)) {
					$c_segment = new cSegments();
					if (is_null($p_segment)) {
						$p_segment = new pSegments();
					}
				}
				$c_segment->setText($regs[1]);
				$this->text = preg_replace('/(^[^<]*)/usm', '', $this->text, 1);
			} else {
				echo "HERE!";die;
				return false;
			}
		}
		if (!is_null($paragraph)) {
			if (!is_null($p_segment)) {
				if (!is_null($c_segment)) {
					$p_segment->addSegment($c_segment);
				}
				$paragraph->addSegment($p_segment);
			}
			$this->addItem('Para', $this->addParagraph($paragraph));
		}
		return true;
	}
	public function getParagraphs(){
		return $this->paragraphs;
	}
	public function removeParagraph($Key){
		unset($this->paragraphs[$Key]);
	}

	protected function rebuildloader(){
	    $str = "";
	    $str .= $this->FileFormat;
	    $str .= $this->FileVersion;
	    $str .= $this->FeatureSet;
	    $str .= $this->ColorTable;
	    return $str;
	}
	protected function rebuildhead(){
	    $str = "";
	    foreach($this->DefineCharStyle as $DefineCharStyle){
			$str .= $DefineCharStyle;
	    }
	    foreach($this->DefineKinsokuStyle as $DefineKinsokuStyle){
		    if(empty($DefineKinsokuStyle)) continue;
		    $str .= $DefineKinsokuStyle;
	    }
	    foreach($this->DefineMojikumiStyle as $DefineMojikumiStyle){
		    if(empty($DefineMojikumiStyle)) continue;
		    $str .= $DefineMojikumiStyle;
	    }
	    foreach($this->DefineListStyle as $DefineListStyle){
		    if(empty($DefineListStyle)) continue;
		    $str .= $DefineListStyle;
	    }
	    foreach($this->DefineCompositeFont as $DefineCompositeFont){
		    if(empty($DefineCompositeFont)) continue;
		    $str .= $DefineCompositeFont;
	    }
	    foreach($this->DefineParaStyle as $DefineParaStyle){
		    if(empty($DefineParaStyle)) continue;
		    $str .= $DefineParaStyle;
	    }
	    return $str;
	}

	public function rebuild() {
		$str = "";
		$str .= $this->rebuildloader();
		$str .= $this->rebuildhead();
		foreach($this->Items as $Item){
		  if($Item[0] == 'Para') $str .= $this->paragraphs[$Item[1]];
		  if($Item[0] == 'Table') $str .= $this->tables[$Item[1]];
		}
		#foreach($this->paragraphs as $paragraph){
		#	$str .= $paragraph;
		#}
		return $str;
	}
	public function getText(){
		$str = "";
		foreach($this->paragraphs as $paragraph){
			$str .= $paragraph->getText();
		}
		return $str;
	}
	public function getArray(){
		$array = array();
		foreach($this->paragraphs as $paragraph){
			$array[] = $paragraph->getText();
		}
		return $array;
	}

	public function reset(){
		$this->current = -1;
	}

	public function masterReset(){
		$this->text = '';
		//Details
		$this->FileFormat = '';
		$this->FileVersion = '';
		$this->FeatureSet = '';

		$this->paragraphs = array();

		//Defined Details
		$this->ColorTable = '';

		$this->DefineCharStyle = array();
		$this->DefineListStyle = array();
		$this->DefineParaStyle = array();
		$this->DefineKinsokuStyle = array();
		$this->DefineMojikumiStyle = array();

		$this->current = -1;
	}

	public function CurrentItem() {
		if (count($this->paragraphs) >= $this->current) {
			return $this->paragraphs[$this->current];
		}
	}
	public function NextItem() {
		if ($this->hasNextItem()) {
			return $this->paragraphs[++$this->current];
		} else {
			$this->reset();
			return null;
		}
	}
	public function hasNextItem() {
		return (count($this->paragraphs)-1 > $this->current);
	}
	public function setCurrent(Paragraph $Paragraph){
		return $this->paragraphs[$this->current] = $Paragraph;
	}
}

class Paragraph{
	private $ParaStyle;
	private $Segments = array();
	protected $current = -1;

	public function  __construct($ParaStyle) {
	  $this->ParaStyle = $ParaStyle;
	}

	public function __clone(){
	  #foreach($this->Segments as $K => $Segment){
	  #  $this->Segments[$K] = clone $Segment;
	  #}
	  /*
	  foreach ($this as $key => $val) {
	    if (is_object($val) || (is_array($val))) {
	      $this->{$key} = unserialize(serialize($val));
	    }
	  }
	  */
	  foreach($this as $name => $value) {
	    if(gettype($value)=='object') {
	      $this->$name= clone($this->$name);
	    }
	  }
	}
	public function addSegment(pSegments $Segment){
		$this->Segments[] = $Segment;
	}
	public function getSegments(){
		return $this->Segments;
	}
	public function getSegment($Key){
		return $this->Segments[$Key];
	}
	public function removeSegment($Key){
		unset($this->Segments[$Key]);
	}
	public function replaceSegment($Key,pSegments $Segment){
		return $this->Segments[$Key] = $Segment;
	}
	public function  __toString() {
		$str = "<ParaStyle:{$this->ParaStyle}>";
		foreach($this->Segments as $Segment){
			$str .= (string)$Segment;
		}
		return $str;
	}

	public function getText(){
		$str = "";
		foreach($this->Segments as $Segment){
			$str .= $Segment->getText();
		}
		return $str;
	}

	public function RemoveAllStyles($except = array()){
		$Styles = array_keys($this->getStyles());
		foreach($Styles as $Style){
			if(!in_array($Style, $except)){
				$this->removeStyle($Style);
			}
		}
	}
	public function RemoveStyles($removals = array()){
		$Styles = array_keys($this->getStyles());
		foreach($Styles as $Style){
			if(in_array($Style, $removals)){
				$this->removeStyle($Style);
			}
		}
	}
	public function Merge(){
		$Previous_pHash = "";
		$Previous_pKey = "";
		foreach($this->Segments as $pKey => $pSegment){
			$pHash = $pSegment->getStylesHash();
			if($pHash == $Previous_pHash){
				$prevSegment = $this->getSegment($Previous_pKey);

				//$prevSegment->setText($prevSegment->getText().$pSegment->getText());
				$cSegments = $pSegment->getSegments();
				foreach($cSegments as $cSegment){
					$prevSegment->addSegment($cSegment);
				}
				$this->replaceSegment($Previous_pKey,$prevSegment);
				$this->removeSegment($pKey);
			}else{
				$Previous_pHash = $pHash;
				$Previous_pKey = $pKey;
			}
		}
		$this->Segments = array_values($this->Segments);
	}

	public function reset(){
		$this->current = -1;
	}

	public function CurrentItem() {
		if (count($this->Segments) >= $this->current) {
			return $this->Segments[$this->current];
		}
	}
	public function NextItem() {
		if ($this->hasNextItem()) {
			return $this->Segments[++$this->current];
		} else {
			$this->reset();
			return null;
		}
	}
	public function hasNextItem() {
		return (count($this->Segments)-1 > $this->current);
	}
	public function setCurrent(pSegments $Paragraph){
		return $this->Segments[$this->current] = $Paragraph;
	}
}
class pSegments{
	private $Styles = array();
	private $cSegments = array();
	protected $current = -1;

	public function __clone(){
	  foreach ($this as $key => $val) {
	    if (is_object($val) || (is_array($val))) {
	      $this->{$key} = unserialize(serialize($val));
	    }
	  }
	}
	public function addSegment(cSegments $Segment){
		$this->cSegments[] = $Segment;
	}
	public function replaceSegment($Key,cSegments $Segment){
		return $this->cSegments[$Key] = $Segment;
	}
	public function removeSegment($Key){
		unset($this->cSegments[$Key]);
	}
	public function getSegments(){
		return $this->cSegments;
	}
	public function getSegment($Key){
		return $this->cSegments[$Key];
	}

	public function addStyle($style,$value){
		$this->Styles[$style] = $value;
	}
	public function removeStyle($style){
		unset($this->Styles[$style]);
	}
	public function getStyles(){
		$Styles = $this->Styles;
		krsort($Styles);
		return $Styles;
	}
	function  __toString() {
		$segments = $this->getSegments();
		$str = "";
		foreach($segments as $segment){
			$str .= (string)$segment;
		}
		$style = $this->getStyles();
		foreach($style as $K => $V){
			$str = "<$K:$V>$str<$K:>";
		}
		return $str;
	}

	public function getText(){
		$str = "";
		foreach($this->cSegments as $Segment){
			$str .= $Segment->getText();
		}
		return $str;
	}

	public function getStylesHash(){
		$Styles = $this->getStyles();
		return md5(serialize($Styles));
	}

	public function Merge(){
		$Previous_cHash = "";
		$Previous_cKey = "";
		foreach($this->cSegments as $cKey => $cSegment){
			$cHash = $cSegment->getStylesHash();
			if($cHash == $Previous_cHash){
				$prevSegment = $this->getSegment($Previous_cKey);
				$prevSegment->setText($prevSegment->getText().$cSegment->getText());
				$this->replaceSegment($Previous_cKey,$prevSegment);
				$this->removeSegment($cKey);
			}else{
				$Previous_cHash = $cHash;
				$Previous_cKey = $cKey;
			}
		}
		$this->cSegments = array_values($this->cSegments);
	}
	public function RemoveAllStyles($except = array()){
		$Styles = array_keys($this->getStyles());
		foreach($Styles as $Style){
			if(!in_array($Style, $except)){
				$this->removeStyle($Style);
			}
		}
	}
	public function RemoveStyles($removals = array()){
		$Styles = array_keys($this->getStyles());
		foreach($Styles as $Style){
			if(in_array($Style, $removals)){
				$this->removeStyle($Style);
			}
		}
	}

	public function reset(){
		$this->current = -1;
	}
	public function CurrentKey(){
		return $this->current;
	}
	public function setItem($Key, cSegments $Segment){
		$this->cSegments[$Key] = $Segment;
	}
	public function CurrentItem() {
		if (count($this->cSegments) >= $this->current) {
			return $this->cSegments[$this->current];
		}
	}
	public function NextItem() {
		if ($this->hasNextItem()) {
			return $this->cSegments[++$this->current];
		} else {
			$this->reset();
			return null;
		}
	}
	public function hasNextItem() {
		return (count($this->cSegments)-1 > $this->current);
	}
	public function setCurrent(cSegments $Paragraph){
		return $this->cSegments[$this->current] = $Paragraph;
	}
}
class cSegments{
	private $Styles = array();
	private $Text = null;

	public function addStyle($style,$value){
		$this->Styles[$style] = $value;
	}
	public function removeStyle($style){
		if(isset($this->Styles[$style])) unset($this->Styles[$style]);
	}
	public function setText($Text){
		$this->Text = $Text;
	}
	public function getText(){
		return $this->Text;
	}
	public function getStyles(){
		return $this->Styles;
	}
	public function getStylesHash(){
		return md5(serialize($this->Styles));
	}

	public function RemoveAllStyles($except = array()){
		$Styles = array_keys($this->getStyles());
		foreach($Styles as $Style){
			if(!in_array($Style, $except)){
				$this->removeStyle($Style);
			}
		}
	}
	public function RemoveStyles($removals = array()){
		$Styles = array_keys($this->getStyles());
		foreach($Styles as $Style){
			if(in_array($Style, $removals)){
				$this->removeStyle($Style);
			}
		}
	}
	function  __toString() {
		$str = $this->getText();
		$style = $this->getStyles();
		krsort($style);
		if(isset($style['cNextXChars'])){
			$cNextXChars = $style['cNextXChars'];
			unset($style['cNextXChars']);
			$style['cNextXChars'] = $cNextXChars;
		}
		if(isset($style['CharStyle'])){
			$CharStyle = $style['CharStyle'];
			unset($style['CharStyle']);
			$style['CharStyle'] = $CharStyle;
		}
		foreach($style as $K => $V){
			if($K == "cNextXChars"){
				$str = "<$K:$V>$str";
			} elseif($K == "CharStyle") {
				if(!empty($V)) {
					$str = "<$K:$V>$str";
				} else {
					$str = "$str<$K:>";
				}
			} else {
				$str = "<$K:$V>$str<$K:>";
			}
		}
		if(empty($str)) return "";
		return $str;
	}
}

class INDD_Story_Rebuilder extends INDD_text_Rebuilder {
    private $StrokeStyleTable = array();
    private $ColorTable = '';
    private $DefineTableStyle = array();

    protected function removeDefined() {
      parent::removeDefined();
      $this->removeDefineTableStyle();
      $this->removeStrokeStyleTable();
    }

    public function masterReset(){
	    //Defined Details
	    $this->ColorTable = '';
	    $this->DefineTableStyle = array();
	    $this->StrokeStyleTable = array();
	    parent::masterReset();
    }

    protected function removeDefineTableStyle(){
	    $DefineTableStyleReg = '/<DefineTableStyle:(.*?)>\r\n/ui';
	    return $this->FindAndRemove($DefineTableStyleReg, $this->DefineTableStyle[]);
    }
    protected function removeStrokeStyleTable(){
	    $StrokeStyleTableReg = '/<StrokeStyleTable(.*?[^\\\\]>)>\r\n/ui';
	    return $this->FindAndRemove($StrokeStyleTableReg, $this->StrokeStyleTable[]);
    }
    protected function rebuildloader() {
      $str = "";
      $str .= parent::rebuildloader();
      $str .= $this->ColorTable;
      return $str;
    }
    protected function rebuildhead() {
		$str = "";
		$str .= parent::rebuildhead();

		foreach ($this->DefineTableStyle as $DefineTableStyle) {
			if (empty($DefineTableStyle)) continue;
			$str .= $DefineTableStyle;
		}
		foreach ($this->StrokeStyleTable as $StrokeStyleTable) {
			if (empty($StrokeStyleTable)) continue;
			$str .= $StrokeStyleTable;
		}
		return $str;
	}
}

class inddTable{
  private $TableStyle;
  private $settings = array();
  function  __construct() {
    $this->settings['tCellDefaultCellType'] = 'Text';
  }
  function addSetting($setting, $value){
    $this->settings[$setting] = $value;
  }
  function setTableStyle($TableStyle){
    $this->TableStyle = $TableStyle;
  }

  function setDefaultCellType($DefaultCellType){
    $this->settings['tCellDefaultCellType'] = $DefaultCellType;
  }

  private $Cols = array();
  function addCol(inddTableCol $col){
    $this->Cols[] = $col;
  }
  private $Rows = array();
  function addRow(inddTableRow $Row){
    $this->Rows[] = $Row;
  }

  function  __toString() {
    //Finish Above
    $Style = '';
    //$Style .= sprintf('<tRowAttrHeight:%f>',$this->height);
    foreach($this->settings as $K => $V){
      $Style .= sprintf('<%s:%s>',$K,$V);
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
class inddTableCol{
  private $settings = array();
  function  __construct($width) {
    $this->setWidth($width);
  }
  function setWidth($width){
    $this->settings['tColAttrWidth'] = $width;
  }
  function addSetting($setting, $value){
    $this->settings[$setting] = $value;
  }
  function  __toString() {
    $Style = '';
    foreach($this->settings as $K => $V){
      $Style .= sprintf('<%s:%s>',$K,$V);
    }
    return "<ColStart:$Style>";
  }
}
class inddTableRow{
  private $cells = array();
  #private $height = 0;
  private $settings = array();

	public function __clone(){
	  foreach ($this as $key => $val) {
	    if (is_object($val) || (is_array($val))) {
	      $this->{$key} = unserialize(serialize($val));
	    }
	  }
	}

  function addSetting($setting, $value){
    $this->settings[$setting] = $value;
  }
  #function setHeight($height){
  #  $this->height = $height;
  #}
  function addCell(inddTableCell $cell){
    $this->cells[] = $cell;
  }
  function  __toString() {
    $ret = "";
    $Style = '';
    //$Style .= sprintf('<tRowAttrHeight:%f>',$this->height);
    foreach($this->settings as $K => $V){
      $Style .= sprintf('<%s:%s>',$K,$V);
    }

    $ret .= "<RowStart:$Style>";
    foreach($this->cells as $cell){
      $ret .= (string)$cell;
    }
    $ret .= "<RowEnd:>";
    return $ret;
  }

}
class inddTableCell{
  private $Paras = array();
  private $CellStyle = null;
  private $StylePriority = null;
  private $CellStart = null;
  private $settings = array();

	public function __clone(){
	  foreach ($this as $key => $val) {
	    if (is_object($val) || (is_array($val))) {
	      $this->{$key} = unserialize(serialize($val));
	    }
	  }
	}

  function addSetting($setting, $value){
    $this->settings[$setting] = $value;
  }
  function addPara(Paragraph &$para){
    $this->Paras[] = $para;
  }
  function getParas(){
    return $this->Paras;
  }
  function setCellStyle($CellStyle){
    $this->CellStyle = $CellStyle;
  }
  function setStylePriority($StylePriority){
    $this->StylePriority = $StylePriority;
  }
  function setCellStart($CellStart1, $CellStart2){
    $this->CellStart = "$CellStart1,$CellStart2";
  }
  function  __toString() {
    $ret = "";
    $ret .= "<CellStyle:{$this->CellStyle}>";
    $ret .= "<StylePriority:{$this->StylePriority}>";
    $Style = '';
    foreach($this->settings as $K => $V){
      $Style .= sprintf('<%s:%s>',$K,$V);
    }

    $ret .= "<CellStart:{$this->CellStart}$Style>";
    foreach($this->Paras as $para){
      $ret .= $para;
    }
    $ret .= "<CellEnd:>";
    return $ret;
  }
}