<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once(PROCESSES . "BaseProcess.php");
//TODO: add Language code check
//http://www.lingoes.net/en/translator/langcode.htm
class XLIFFParsaExt extends Process {
	protected $XLIFFsourceLang = null;
	protected $XLIFFtargetLang = null;
	
	function LangCodesConvert($lang = 'gb') {
		$TransCodes = array(
			"ae" => "ar",
			"cn" => "zh",
			"cz" => "cs",
			"dk" => "da",
			"gb" => "en",
			"us" => "en",
			"gr" => "el",
			"il" => "iw",
			"jp" => "ja",
			"kr" => "ko",
			"pk" => "ur",
			"se" => "sv",
			"tw" => "zh",
			"vn" => "vi",
			"in" => "hi",
			
		);
		return (isset($TransCodes[$lang])) ? $TransCodes[$lang]."-".strtoupper($lang) : $lang."-".strtoupper($lang);
	}
	
	function CreateXLFFile($ArtworkID, $TaskID, $lines=0, $Strict=false) {
		require_once dirname(__FILE__).'/../engines/XLIFF.php';
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$ArtworkID = $row['artworkID'];
		$ArtworkName = $row['artworkName'];
		$SourceLangID = $row['sourceLanguageID'];
		$SourceLangFlag = substr($row['SourceLangFlag'], 0, 2);
		$TargetLangID = $row['desiredLanguageID'];
		$TargetLangFlag = substr($row['TargetLangFlag'], 0, 2);
		
		$this->XLIFFsourceLang = $this->LangCodesConvert($SourceLangFlag);
		$this->XLIFFtargetLang = $this->LangCodesConvert($TargetLangFlag);
		/*
		$query_inCheckRs = sprintf("SELECT content FROM instructions WHERE sourceLangID = %d", $SourceLangID);
		$inCheckRs = mysql_query($query_inCheckRs) or die(mysql_error());
		$totalRows_inCheckRs = mysql_num_rows($inCheckRs);
		$SLID = ($totalRows_inCheckRs > 0) ? $SourceLangID : 1;
		$query_instructionRs = sprintf("SELECT content FROM instructions WHERE sourceLangID = %d", $SLID);
		$instructionRs = mysql_query($query_instructionRs) or die(mysql_error());
		$row_instructionRs = mysql_fetch_assoc($instructionRs);

		$notes = $row_instructionRs['content'];
		*/
		$notes = 'No Notes';
		
		$xliff = new XLIFF($this->XLIFFsourceLang, $this->XLIFFtargetLang, $Strict, $notes);
		$exportPara = $this->ExportPara($ArtworkID, $TaskID);

		$paras = count($exportPara);
		if ($paras == 0) return false;

		$counter = (!empty($lines))?$lines:$paras;

		$Required = false;
		$Export = Array();
		foreach ($exportPara as $row) {
			if ($counter == 0) break;
			$story_info = $this->GetStoryInfoByPL($row['PL']);

			if($story_info === false) continue;
			$SG = $story_info['StoryGroup'];
			$SO = $story_info['order'];

			$Trans = $this->TranslateText($row['ParaID'], $TargetLangID, $SourceLangID);
			if ($Trans['LC'] == 0) {
				$Export[$SG][] = Array((int)$row['ParaID'],$row['ParaText'], '');
				$Required = true;
				$counter--;
			} else {
				if (empty($lines)) {
					$Export[$SG][] = Array((int)$row['ParaID'],$row['ParaText'], $Trans['Para']);
					$Required = true;
					$counter--;
				}
			}
		}
		foreach($Export as $SG => $E){
			$Paras = Array();
			$Trans = Array();
			foreach($E as $Para){
				$pID = $Para[0];
				$Paras[] = Array($pID,$Para[1]);
				$Trans[] = Array($pID,$Para[2]);
			}
			if(count($Paras)>1){
				$xliff->addPhrases($Paras, $Trans);
			}else{
				$Para = current($Paras);
				$Tran = current($Trans);
				$xliff->addPhrase($Para[1], $Tran[1]);
			}
		}
		
		if (!$Required) return false;
		$contents = $xliff->getDocument();
		
		$File = $ArtworkName . "_" . $SourceLangFlag . "_to_" . $TargetLangFlag;
		if (!empty($lines)) {
			$File .= "_sample";
		}
		$File .= ".XLF";
		//$contents Save to $File
		file_put_contents(ROOT . TMP_DIR . $File, $contents);
		return $File;
	}
	
	function export($ArtworkID, $TaskID, $lines=0) {
		return $this->CreateXLFFile($ArtworkID, $TaskID, $lines, True);
	}
	
	function import($ArtworkID, $TaskID, $file, $option=1, $CS=false) {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return $this->errorReport($ArtworkID,'Import Error, No Task found');
		$sourceLanguageID = $row['sourceLanguageID'];
		$TargetLangID = $row['desiredLanguageID'];
		$SourceLangFlag = substr($row['SourceLangFlag'], 0, 2);
		$TargetLangFlag = substr($row['TargetLangFlag'], 0, 2);
		$this->XLIFFsourceLang = $this->LangCodesConvert($SourceLangFlag);
		$this->XLIFFtargetLang = $this->LangCodesConvert($TargetLangFlag);
		
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		$xml = new DOMDocument('1.0','UTF-8');
		$loaded = $xml->load($file);
		if ($loaded === false) return $this->errorReport($ArtworkID,'Import Error, Unable to load file');
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;
		
		$xliff = $xml->getElementsByTagName('xliff');
		if($xliff->length == 0) return $this->errorReport($ArtworkID,'Import Error, malformatted file [attr:xliff]');
		$file = $xliff->item(0)->getElementsByTagName('file');
		if($file->length == 0) return $this->errorReport($ArtworkID,'Import Error, malformatted file [attr:file]');
		
		$xliffSource = $file->item(0)->getAttribute('source-language');
		$xliffTarget = $file->item(0)->getAttribute('target-language');
		
		if(!empty($xliffSource) && $xliffSource!=$this->XLIFFsourceLang){
			$this->errorReport($ArtworkID,"Import Error, Language Source mismatch [{$this->XLIFFsourceLang}:$xliffSource]");
		}
		if(!empty($xliffTarget) && $xliffTarget!=$this->XLIFFtargetLang){
			$this->errorReport($ArtworkID,"Import Error, Language Target mismatch [{$this->XLIFFtargetLang}:$xliffTarget]");
		}
		
		$body = $file->item(0)->getElementsByTagName('body');
		if($body->length == 0) return false;
		$units = $body->item(0)->getElementsByTagName('trans-unit');
		$loose = $CS === true ? 0 : 1;
		$import_id = $this->ImportStart($ArtworkID, $TaskID, "XLF", $option, $loose);
		$imported = false;

		foreach($units as $unit){
			$source = $unit->getElementsByTagName('source');
			$target = $unit->getElementsByTagName('target');
			if($source->length ==0) continue;
			if($target->length ==0) continue;
			$OrgPara = (string)$source->item(0)->nodeValue;
			$TransPara = (string)$target->item(0)->nodeValue;
			//Has G's
			$sG = $source->item(0)->getElementsByTagName('g');
			$tG = $target->item(0)->getElementsByTagName('g');
			if($sG->length > 0 && $sG->length==$tG->length){
				$Gs = Array();
				for($n=0;$n<$sG->length;$n++){
					$gOPID = $sG->item($n)->getAttribute('id');
					$Gs[$gOPID][0][0] = $n;
					$Gs[$gOPID][0][1] = (string)$sG->item($n)->nodeValue;
					
					$gTPID = $tG->item($n)->getAttribute('id');
					$Gs[$gTPID][1][0] = $n;
					$Gs[$gTPID][1][1] = (string)$tG->item($n)->nodeValue;
				}
				foreach($Gs as $gID => $G){
					$OrgPara = $G[0][1];
					$TransPara = $G[1][1];
					if($G[0][0]!=$G[1][0]){
						#echo "'$OrgPara' Moved from {$G[0][0]} to {$G[1][0]}\n";
					}
					$this->importPara($import_id, $OrgPara,$TransPara,$sourceLanguageID, $TargetLangID, $CS, $TaskID, $brandID, $subjectID);
				}
				continue;
			}else{
				$okay = $this->importPara($import_id, $OrgPara,$TransPara,$sourceLanguageID, $TargetLangID, $CS, $TaskID, $brandID, $subjectID);
				if(!$okay) continue;
			}
			$imported = true;
		}
		$this->ImportEnd($import_id);
		if (!$imported) return false;
		return $import_id;
	}
	
	function importPara($import_id, $OrgPara,$TransPara,$sourceLanguageID, $TargetLangID, $CS, $TaskID, $brandID, $subjectID){
		if (empty($OrgPara) || empty($TransPara)) return false;
		$source_para_row = $this->ParaExists($OrgPara, $sourceLanguageID, $CS);
		//Get ParaGroup from Database;
		if ($source_para_row === false || (empty($option) && $TransPara == $OrgPara)) {
			$this->AddImportRow($import_id, $OrgPara, $TransPara, 0);
		} else {
			$PG = $source_para_row['PG'];
			$this->AddTranslated($TransPara, $TargetLangID, $PG, $sourceLanguageID, $TaskID, $_SESSION['userID'], PARA_IMPORT, $brandID, $subjectID);
			$this->AddImportRow($import_id, $OrgPara, $TransPara, 1);
		}
		return true;
	}
	
	function errorReport($ArtworkID,$Message){
		$this->LogSystemEvent($_SESSION['userID'],$Message,0,$ArtworkID);
		return false;
	}
	
}
//Don't Force Target
class XLIFFParsaExtGlobal extends XLIFFParsaExt{
	function export($ArtworkID, $TaskID, $lines=0) {
		return $this->CreateXLFFile($ArtworkID, $TaskID, $lines, False);
	}
}
/*
$taskID = (isset($_GET['taskID'])) ? (int)$_GET['taskID'] : 0;
header("Content-Type: text/html;charset=UTF-8");
$X = new XLIFFParsaExt();
//$X->test($taskID);
#$file = ROOT . TMP_DIR . $X->CreateXLFFile(1, 1);
#echo $file;
$file = 'E:\localhost\alpha/tmp/test_gb_to_es.XLF';
$X = $X->import(1, 1, $file, 1);
var_dump($X);
*/