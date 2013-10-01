<?php
require_once(PROCESSES . "BaseProcess.php");

class XLIFFParsa extends Process {
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
			"in" => "hi"
		);
		return (isset($TransCodes[$lang])) ? $TransCodes[$lang]."-".strtoupper($lang) : $lang;
	}

	function CreateXLFFile($ArtworkID, $TaskID, $lines=0, $Author="", $Operator="", $Company="") {
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

		$xliff = new XLIFF($this->LangCodesConvert($SourceLangFlag), $this->LangCodesConvert($TargetLangFlag));
		$exportPara = $this->ExportPara($ArtworkID, $TaskID);

		$paras = count($exportPara);
		if ($paras == 0) return false;

		if (!empty($lines)) {
			$counter = $lines;
		} else {
			$counter = $paras;
		}

		// instructions
		/*
		$query_inCheckRs = sprintf("SELECT content FROM instructions WHERE sourceLangID = %d", $SourceLangID);
		$inCheckRs = mysql_query($query_inCheckRs) or die(mysql_error());
		$totalRows_inCheckRs = mysql_num_rows($inCheckRs);
		$SLID = ($totalRows_inCheckRs > 0) ? $SourceLangID : 1;
		$query_instructionRs = sprintf("SELECT content FROM instructions WHERE sourceLangID = %d", $SLID);
		$instructionRs = mysql_query($query_instructionRs) or die(mysql_error());
		$row_instructionRs = mysql_fetch_assoc($instructionRs);

		$p = $helper->CreatePara($row_instructionRs['content'], 'auto');
		$body->appendChild($p);
		$p = $helper->CreateEmptyPara();
		$body->appendChild($p);
		 *
		 */

		$Required = false;
		foreach ($exportPara as $row) {
			if ($counter == 0) break;
			$Trans = $this->TranslateText($row['ParaID'], $TargetLangID, $SourceLangID);
			if ($Trans['LC'] == 0) {
				#$Para = str_replace("\r\n", "\n", $row['ParaText']);
				$xliff->addPhrase($row['ParaText'], '');
				$Required = true;
				$counter--;
			} else {
				//Add Translated to DOCX if not sample DOCX
				if (empty($lines)) {
					$xliff->addPhrase($row['ParaText'], $Trans['Para']);
					$Required = true;
					$counter--;
				}
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
		return $this->CreateXLFFile($ArtworkID, $TaskID, $lines);
	}

	function import($ArtworkID, $TaskID, $file, $option=1, $CS=false) {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$sourceLanguageID = $row['sourceLanguageID'];
		$TargetLangID = $row['desiredLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		$xml = new DOMDocument('1.0','UTF-8');
		$load = $xml->load($file);
		if ($loaded === false) return false;
		$xml->formatOutput = true;
		$xml->preserveWhiteSpace = false;
		
		$xliff = $xml->getElementsByTagName('xliff');
		if($xliff->length == 0) return false;
		$file = $xliff->item(0)->getElementsByTagName('file');
		if($file->length == 0) return false;
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
			if (empty($OrgPara) || empty($TransPara)) continue;

			$source_para_row = $this->ParaExists($OrgPara, $sourceLanguageID, $CS);
			//Get ParaGroup from Database;
			if ($source_para_row === false || (empty($option) && $TransPara == $OrgPara)) {
				$this->AddImportRow($import_id, $OrgPara, $TransPara, 0);
			} else {
				$PG = $source_para_row['PG'];
				$this->AddTranslated($TransPara, $TargetLangID, $PG, $sourceLanguageID, $TaskID, $_SESSION['userID'], PARA_IMPORT, $brandID, $subjectID);
				$this->AddImportRow($import_id, $OrgPara, $TransPara, 1);
			}
			$imported = true;
		}
		$this->ImportEnd($import_id);
		if (!$imported) return false;
		return $import_id;
	}
}