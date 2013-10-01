<?php
/**
 * Description of DocX
 *
 * @author MadTechie
 */
require_once(PROCESSES."DOCX.php");
class TradosParsa extends DocXParsa {
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
			$Para = str_replace("\r\n","\n",$row['ParaText']);
			$p = $helper->CreatePara($Para);
			$body->appendChild($p);
			$Required = true;
			$counter--;
		}
		if($Required === false) return false;

		$contents = $this->oXML->saveXML();

		$this->DOCX->setSection('word/document.xml',$contents);

		$File = "Trados_Full_".$ArtworkName."_".$SourceLangFlag."_to_".$TargetLangFlag;
		if(!empty($lines)) {
			$File .= "_sample";
		}
		$File .= ".DOCX";
		$this->DOCX->reBuild(ROOT.TMP_DIR.$File);
		return $File;
	}

	function export($ArtworkID, $TaskID, $lines=0) {
		return $this->CreateDOCXFile($ArtworkID,$TaskID,$lines);
	}

	function import($ArtworkID, $TaskID, $file, $option=1, $CS=true) {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$sourceLanguageID = $row['sourceLanguageID'];
		$TargetLangID = $row['desiredLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		//Read in Trados file
		//get array fo Groups
		//loop groups
		//	extract source (concat g's)
		//	extract target (concat mrk's)
		//	check source para get PG .....
		//	Add para......
		//end loop

		$DOM = new DOMDocument('1.0','UTF-8');
		$loaded = @$DOM->load($file);
		if($loaded === false) return false;
		$Groups = $DOM->getElementsByTagName('group');
		if($Groups->length == 0) return false;
		$loose = $CS===true ? 0 : 1 ;
		$import_id = $this->ImportStart($ArtworkID,$TaskID,"SDLXLIFF",$option,$loose);
		$imported = false;
		foreach($Groups as $Group){
			$Source = $Group->getElementsByTagName('source');
			$Target = $Group->getElementsByTagName('target');

			if($Source->length == 0) continue;
			if($Target->length == 0) continue;
			
			$Source = $Source->item(0);
			$Target = $Target->item(0);

			$gs = $Source->getElementsByTagName('g');
			if($gs->length == 0) continue;
			$SourcePara  = "";
			$counter = 0;
			$tmp_id = 0;
			foreach($gs as $g) {
				$counter++;
				$g_id = $g->getAttribute('id');
				if($tmp_id != $g_id) {
					$tmp_id = $g_id;
					if($counter > 1) $SourcePara .= "\n";
				}
				$SourcePara .= $g->nodeValue;
				/*
				if($gs->length != $counter){
					$SourcePara .= "\n";
				}
				if(empty($g->nodeValue)){
					$SourcePara .= "\n";
				}
				*/
			}
			if(empty($SourcePara)) continue;
			/*
			//OLD
			$mrks = $Target->getElementsByTagName('mrk');
			if($mrks->length == 0) continue;
			$TargetPara  = "";
			foreach($mrks as $mrk){
				$TargetPara .= $mrk->nodeValue;
			}
			//*/

			//NEW
			$gs = $Target->getElementsByTagName('g');
			if($gs->length == 0) continue;
			foreach($gs as $g) {
			  $counter++;
			  $mrks = $g->getElementsByTagName('mrk');
			  if($mrks->length == 0) continue;
			  $TargetPara  = "";
			  preg_match_all('%([^>]*)<mrk>%i', $DOM->saveXML($g), $prefix, PREG_PATTERN_ORDER);
			  preg_match_all('%</mrk>([^<]*)%i', $DOM->saveXML($g), $append, PREG_PATTERN_ORDER);
			  $prefix = $prefix[1];
			  $append = $append[1];
			  foreach($mrks as $k => $mrk) {
			    $TargetPara .= $prefix[$k].$mrk->nodeValue.$append[$k];
			  }
			}
			if(empty($TargetPara)) continue;
			$source_para_row = $this->ParaExists($SourcePara,$sourceLanguageID,$CS);
			//Get ParaGroup from Database;
			if($source_para_row === false || (empty($option) && $TargetPara==$SourcePara)){
				// add source para
				$this->AddImportRow($import_id,$SourcePara,$TargetPara,0);
			} else {
				$PG = $source_para_row['PG'];
				$this->AddTranslated($TargetPara,$TargetLangID,$PG,$sourceLanguageID,$TaskID,$_SESSION['userID'],PARA_IMPORT,$brandID,$subjectID);
				$this->AddImportRow($import_id,$SourcePara,$TargetPara,1);
			}
			$imported = true;
		}
		$this->ImportEnd($import_id);
		if($imported === false) return false;
		return $import_id;
	}
}
?>