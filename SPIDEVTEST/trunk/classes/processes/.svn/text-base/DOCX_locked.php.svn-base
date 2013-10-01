<?php
/**
 * Description of DocX
 *
 * @author MadTechie
 */
require_once(PROCESSES."DOCX.php");
require ENGINES . 'OpenXML/DocX_helper.php';
class DocXParsaProtected extends DocXParsa {
	function CreateDOCXFile($ArtworkID, $TaskID, $lines=0, $Author="", $Operator="", $Company="") {
		if(empty($TaskID)) return false;
		if(empty($ArtworkID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$ArtworkName = $row['artworkName'];
		$SourceLangID = $row['sourceLanguageID'];
		$SourceLangFlag = substr($row['SourceLangFlag'], 0, 2);
		$TargetLangID = $row['desiredLanguageID'];
		$TargetLangFlag = substr($row['TargetLangFlag'], 0, 2);

		$exportPara = $this->ExportPara($ArtworkID,$TaskID,false);

		$paras = count($exportPara);
		if($paras==0) return false;

		$counter =(!empty($lines))?$lines:$paras;

		$BaseDOCX = RESOURCES.'BaseProt.docx';
		$this->ReadDOCX($BaseDOCX);

		$helper =  new Protected_DocX_helper($this->oXML);
		$body = $this->oXML->getElementsByTagName('body')->item(0);
		$sectPr = $this->oXML->getElementsByTagName('sectPr')->item(0);

		$query_inCheckRs = sprintf("SELECT content FROM instructions WHERE sourceLangID = %d", $SourceLangID);
		$inCheckRs = mysql_query($query_inCheckRs) or die(mysql_error());
		$totalRows_inCheckRs = mysql_num_rows($inCheckRs);

		$SLID = ($totalRows_inCheckRs>0) ? $SourceLangID : 1;

		$query_instructionRs = sprintf("SELECT content FROM instructions WHERE sourceLangID = %d", $SLID);
		$instructionRs = mysql_query($query_instructionRs) or die(mysql_error());
		$row_instructionRs = mysql_fetch_assoc($instructionRs);

		$p = $helper->CreatePara($row_instructionRs['content'],'auto');
		$body->appendChild($p);
		$p = $helper->CreateEmptyPara();
		$body->appendChild($p);
		$Required = false;
		foreach($exportPara as $row) {
			if($counter==0) break;
			
			$Trans = $this->TranslateText($row['ParaID'], $TargetLangID, $SourceLangID);
			$Para = $row['ParaText'];
			if ($Trans['LC'] == 0) {
				///*
				$amended = $this->GetAmendedPara($row['PL']);
				//Add amended to DOCX if has amended and not sample DOCX
				if($amended!==false && empty($lines)) $Para = $amended['ParaText'];
				//$Para = str_replace("\r\n", "\n", $Para);
				//*/
				$p = $helper->CreateOrgPara($Para);
				$body->appendChild($p);
				$p = $helper->CreateTransPara($Para);
				$body->appendChild($p);
				$Required = true;
				$counter--;
			}else{
				$Trans = $Trans['Para'];
				#$Trans = str_replace("\r\n", "\n", $Trans);
				#$Para = str_replace("\r\n", "\n", $Para);
				$p = $helper->CreateOrgPara($Para);
				$body->appendChild($p);
				$p = $helper->CreateTransPara($Trans);
				$body->appendChild($p);
				$Required = true;
				$counter--;
			}
		}
		if(!$Required) return false;
		//Move w:sectPr to end of body
		$body->appendChild($sectPr);
		
		$contents = $this->oXML->saveXML();

		$valid = $this->DOCX->setSection('word/document.xml',$contents);
		if($valid === false) return false;
		$File = $ArtworkName."_".$SourceLangFlag."_amend";
		if(!empty($lines)) {
			$File .= "_sample";
		}
		$File .= ".DOCX";
		$this->DOCX->reBuild(ROOT.TMP_DIR.$File);
		return $File;
	}

	function import($ArtworkID, $TaskID, $file, $option=1, $CS=true) {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$sourceLangID = $row['sourceLanguageID'];
		$TargetLangID = $row['desiredLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		$Decoded = $this->ReadDOCX($file);
		if(!$Decoded) return false;
		$loose = $CS===true ? 0 : 1 ;
		$import_id = $this->ImportStart($ArtworkID,$TaskID,"DOCX",$option,$loose);
		$par = $this->getParagraphs();
		$imported = false;
		//Translated
		//Original
		foreach($par as $key=>$p) {
			if(empty($p["Original"]) || empty($p["Translated"])) continue;
			$OrgPara = $p["Original"];
			$TranslatedPara = $p["Translated"];
			$source_para_row = $this->ParaExists($OrgPara,$sourceLangID,$CS);
			//Get ParaGroup from Database;
			if($source_para_row === false || (empty($option) && $TranslatedPara==$OrgPara)) {
				$this->AddImportRow($import_id,$OrgPara,$TranslatedPara,0);
			} else {
				/*
				//we have to loop through all the PLs with ParaID in this artwork
				$source_para_id = $source_para_row['ParaID'];
				$new_para_row = $this->AddParagraph($TranslatedPara,$sourceLangID,0,$_SESSION['userID'],PARA_USER,$brandID,$subjectID);
				if($new_para_row === false) continue;
				$new_para_id = $new_para_row['ParaID'];
				$query = sprintf("SELECT paralinks.uID, paralinks.BoxID
								FROM paralinks
								LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
								LEFT JOIN pages ON boxes.PageID = pages.uID
								WHERE pages.ArtworkID = %d
								AND paralinks.ParaID = %d
								ORDER BY paralinks.uID ASC",
								$ArtworkID,
								$source_para_id);
				$result = mysql_query($query) or die(mysql_error());
				while($row = mysql_fetch_assoc($result)) {
					$PL = $row['uID'];
					$BoxID = $row['BoxID'];
					$amended_para_row = $this->GetAmendedPara($PL);
					if($amended_para_row !== false) {
						$amended_para_id = $amended_para_row['ParaID'];
						if($amended_para_id == $new_para_id) continue;
					}
					$this->AmendPara($PL,$new_para_id,$_SESSION['userID']);
					//added for cache optimisation
					$this->AddChangedItem($ArtworkID,$BoxID,$TaskID);
				}
				*/
				$PG = $source_para_row['PG'];
				$this->AddTranslated($TranslatedPara, $TargetLangID, $PG, $sourceLangID, $TaskID, $_SESSION['userID'], PARA_IMPORT, $brandID, $subjectID);

				$this->AddImportRow($import_id,$OrgPara,$TranslatedPara,1);
			}
			$imported = true;
		}
		$this->ImportEnd($import_id);
		if(!$imported) return false;
		return $import_id;
	}
	public function getParagraphs() {
		$body = $this->oXML->getElementsByTagName('body')->item(0);
		$paragraphs = $body->getElementsByTagName('p');

		$returnValue = array();
		$lastPara = null;
		$Key = 0;
		#$returnValue['Original'] = array();
		#$returnValue['Translated'] = array();
		foreach ($paragraphs as $paragraph) {
			$pPr = $paragraph->getElementsByTagName('pPr');
			if($pPr->length==0) continue;
			$pPr = $pPr->item(0);
			
			$pStyle = $pPr->getElementsByTagName('pStyle');
			if($pStyle->length==0) continue;
			$pStyle = $pStyle->item(0);
			if (!$pStyle->hasAttribute('w:val')) continue;
			$val = $pStyle->getAttribute('w:val');
			
			$RList = $paragraph->getElementsByTagName('r');
			if(!$RList->length) continue;
			
			if($lastPara!=$val){
				$lastPara = $val;
				if($val=='Original'){
					$Key++;
				}
			}elseif($val=='Translated'){
				$returnValue[$Key][$val] .= "\n";
			}
			foreach ($RList as $K => $R) {
				$elements = $this->xPath->query(".//w:t|w:br", $R);
				if (!is_null($elements)) {
					foreach ($elements as $element) {
						switch (strtolower($element->tagName)) {
							case "w:t":
								$returnValue[$Key][$val] .= $element->nodeValue;
							break;
							case "w:br":
								$returnValue[$Key][$val] .= "\n";
							break;
						}
					}
				}
			}
		}
		return $returnValue;
	}
}

class Protected_DocX_helper extends DocX_helper{
  function CreateEmptyPara(){
    $p = $this->XML->createElement("w:p");
    $pPr = $this->XML->createElement("w:pPr");
    $rPr = $this->XML->createElement("w:rPr");

	$rFonts = $this->XML->createElement("w:rFonts");
	$rFonts->appendChild($this->createAttribute('w:ascii','Arial Unicode MS'));
	$rFonts->appendChild($this->createAttribute('w:eastAsia','Arial Unicode MS'));
	$rFonts->appendChild($this->createAttribute('w:hAnsi','Arial Unicode MS'));
	$rFonts->appendChild($this->createAttribute('w:cs','Arial Unicode MS'));
	$rPr->appendChild($rFonts);
	
    $pPr->appendChild($rPr);
    $p->appendChild($pPr);
    return $p;
  }
  function CreateOrgPara($ParaText){
    $p = $this->XML->createElement("w:p");
    $pPr = $this->XML->createElement("w:pPr");
    $pStyle = $this->XML->createElement("w:pStyle");
    $pStyle->appendChild($this->createAttribute('w:val','Original'));
    $pPr->appendChild($pStyle);
    $p->appendChild($pPr);
    
    $r = $this->XML->createElement("w:r");
    $Paras = explode("\n", $ParaText);
    foreach($Paras as $ParaKey=>$Para){
      $t = $this->XML->createElement("w:t");
      $t->appendChild($this->createAttribute('xml:space','preserve'));
      $t->nodeValue = htmlspecialchars($Para);
      if($ParaKey > 0){
		$r->appendChild($this->XML->createElement("w:br"));
      }
      $r->appendChild($t);
    }
	$p->appendChild($r);
    return $p;
  }
  
  function CreateTransPara($ParaText){
    $sdt = $this->XML->createElement("w:sdt");
    $sdtContent = $this->XML->createElement("w:sdtContent");
    
    $p = $this->XML->createElement("w:p");
    $pPr = $this->XML->createElement("w:pPr");
    $pStyle = $this->XML->createElement("w:pStyle");
    $pStyle->appendChild($this->createAttribute('w:val','Translated'));
    $pPr->appendChild($pStyle);
    $p->appendChild($pPr);
    
    $r = $this->XML->createElement("w:r");
    $Paras = explode("\n", $ParaText);
    foreach($Paras as $ParaKey=>$Para){
      $t = $this->XML->createElement("w:t");
      $t->appendChild($this->createAttribute('xml:space','preserve'));
      $t->nodeValue = htmlspecialchars($Para);
      if($ParaKey > 0){
		$r->appendChild($this->XML->createElement("w:br"));
      }
      $r->appendChild($t);
    }
    $p->appendChild($r);
    $sdtContent->appendChild($p);
    $sdt->appendChild($sdtContent);
    return $sdt;
  }
}
?>