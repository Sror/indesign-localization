<?php
/**
 * Description of DocX
 *
 * @author MadTechie
 */
require_once(PROCESSES."DOCX.php");
class DocXParsaA extends DocXParsa {
	function CreateDOCXFile($ArtworkID, $TaskID, $lines=0, $Author="", $Operator="", $Company="") {
		if(empty($ArtworkID) || !empty($TaskID)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$ArtworkName = $row['artworkName'];
		$SourceLangID = $row['sourceLanguageID'];
		$SourceLangFlag = substr($row['flag'],0,2);

		$exportPara = $this->ExportPara($ArtworkID,$TaskID,false);

		$paras = count($exportPara);
		if($paras==0) return false;

		if(!empty($lines)) {
			$counter = $lines;
		} else {
			$counter = $paras;
		}

		$BaseDOCX = RESOURCES.'Base.docx';
		$this->ReadDOCX($BaseDOCX);

		$helper =  new DocX_helper($this->oXML);
		$body = $this->oXML->getElementsByTagName('body')->item(0);

		$query_inCheckRs = sprintf("
	    SELECT content FROM instructions
	    WHERE sourceLangID = %d", $SourceLangID);
		$inCheckRs = mysql_query($query_inCheckRs) or die(mysql_error());
		$totalRows_inCheckRs = mysql_num_rows($inCheckRs);

		$SLID = ($totalRows_inCheckRs>0) ? $SourceLangID : 1;

		$query_instructionRs = sprintf("SELECT content
				    FROM instructions
				    WHERE sourceLangID = %d",
		$SLID);
		$instructionRs = mysql_query($query_instructionRs) or die(mysql_error());
		$row_instructionRs = mysql_fetch_assoc($instructionRs);

		$p = $helper->CreatePara($row_instructionRs['content'],'auto');
		$body->appendChild($p);
		$p = $helper->CreateEmptyPara();
		$body->appendChild($p);

		$Required = false;
		foreach($exportPara as $row) {
			if($counter==0) break;
			$Para = $row['ParaText'];
			$amended = $this->GetAmendedPara($row['PL']);
			//Add amended to DOCX if has amended and not sample DOCX
			if($amended!==false && empty($lines)) {
				$Para = $amended['ParaText'];
			}
			$p = $helper->CreateEmptyPara();
			$body->appendChild($p);
			$p = $helper->CreatePara($row['ParaText'],OrgFill);
			$body->appendChild($p);
			$p = $helper->CreatePara($Para,TransFill);
			$body->appendChild($p);
			$Required = true;
			$counter--;
		}
		if(!$Required) return false;

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
		if(empty($ArtworkID) || !empty($TaskID)) return false;
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$sourceLangID = $row['sourceLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		$Decoded = $this->ReadDOCX($file);
		if(!$Decoded) return false;
		$loose = $CS===true ? 0 : 1 ;
		$import_id = $this->ImportStart($ArtworkID,$TaskID,"DOCX",$option,$loose);
		$par = $this->getParagraphs();
		$imported = false;
		foreach($par as $key=>$p) {
			if(empty($p[OrgFill]) || empty($p[TransFill])) continue;
			$OrgPara = $p[OrgFill];
			$AmendedPara = $p[TransFill];
			$source_para_row = $this->ParaExists($OrgPara,$sourceLangID,$CS);
			//Get ParaGroup from Database;
			if($source_para_row === false || (empty($option) && $AmendedPara==$OrgPara)) {
				$this->AddImportRow($import_id,$OrgPara,$AmendedPara,0);
			} else {
				//we have to loop through all the PLs with ParaID in this artwork
				$source_para_id = $source_para_row['ParaID'];
				$new_para_row = $this->AddParagraph($AmendedPara,$sourceLangID,0,$_SESSION['userID'],PARA_USER,$brandID,$subjectID);
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
				$this->AddImportRow($import_id,$OrgPara,$AmendedPara,1);
			}
			$imported = true;
		}
		$this->ImportEnd($import_id);
		if(!$imported) return false;
		return $import_id;
	}
}
?>