<?php
/**
 * Description of DocX
 *
 * @author MadTechie
 */
require_once(PROCESSES."Trados.php");
class TradosLiteParsa extends TradosParsa {
	function CreateDOCXFile($ArtworkID, $TaskID, $lines=0, $Author="", $Operator="", $Company="") {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$ArtworkID = $row['artworkID'];
		$ArtworkName = $row['artworkName'];
		$SourceLangID = $row['sourceLanguageID'];
		$SourceLangFlag = substr($row['SourceLangFlag'],0,2);
		$TargetLangID = $row['desiredLanguageID'];
		$TargetLangFlag = substr($row['TargetLangFlag'],0,2);
		
		$exportPara = $this->ExportPara($ArtworkID,$TaskID);

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
		
		$Required = false;
		foreach($exportPara as $row) {
			if($counter==0) break;
			$Trans = $this->TranslateText($row['ParaID'], $TargetLangID, $SourceLangID);
			if($Trans['LC'] == 0){
				$Para = str_replace("\r\n","\n",$row['ParaText']);
				$p = $helper->CreatePara($Para);
				$body->appendChild($p);
				$Required = true;
				$counter--;
				$qualified++;
			}
		}
		if($Required === false) return false;

		$contents = $this->oXML->saveXML();

		$this->DOCX->setSection('word/document.xml',$contents);

		$File = "Trados_Lite_".$ArtworkName."_".$SourceLangFlag."_to_".$TargetLangFlag;
		if(!empty($lines)) {
			$File .= "_sample";
		}
		$File .= ".DOCX";
		$this->DOCX->reBuild(ROOT.TMP_DIR.$File);
		return $File;
	}

	function export($ArtworkID, $TaskID, $lines=0) {
		return $this->CreateDOCXFile($ArtworkID, $TaskID,$lines);
	}
}
?>