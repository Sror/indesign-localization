<?php

/**
 * Description of DocX
 *
 * @author MadTechie
 */
define("OrgFill", "FCFCFC");
define("TransFill", "D9D9D9");
define("LastInstruction", '***********************************');
require_once(PROCESSES . "BaseProcess.php");

class DocXParsa extends Process {

	protected $DOCX;
	protected $oXML;
	protected $xPath;

	function ReadDOCX($file) {
		require_once(ENGINES . 'OpenXML/DOCX.php');
		$this->DOCX = new DOCX($file);
		$this->oXML = new DOMDocument('1.0', 'UTF-8');
		$contents = $this->DOCX->getSection('word/document.xml');
		if ($contents === false)
			$contents = $this->DOCX->getSection('word\document.xml');
		if (is_null($contents) || $contents === false)
			return false;
		$contents = $contents->getValue();
		$loaded = $this->oXML->loadXML($contents->asXML(), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
		$this->oXML->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w', 'root');
		$this->xPath = new DOMXPath($this->oXML);
		return true;
	}

	private function get_pPr($paragraph) {
		$shdFill = null;
		$pPr = $paragraph->getElementsByTagName('pPr');
		if (!$pPr->length)
			return null;

		$shd = $pPr->item(0)->getElementsByTagName('shd');
		if ($shd->length) {
			$shd = $shd->item(0);
			if ($shd->hasAttribute('w:fill')) {
				$shdFill = $shd->getAttribute('w:fill');
			}
		} else {
			$shdFill = '';
		}
		return $shdFill;
	}

	public function getParagraphs() {
		$body = $this->oXML->getElementsByTagName('body')->item(0);
		$paragraphs = $body->getElementsByTagName('p');

		$returnValue = array();
		$lastFill = "none";
		$Key = 0;
		$returnValue[$Key] = array();
		foreach ($paragraphs as $paragraph) {
			$shdFill = null;
			$RList = $paragraph->getElementsByTagName('r');
			if (!$RList->length)
				continue;
			$rPr = $RList->item(0)->getElementsByTagName('rPr');
			if ($rPr->length) {
				$shd = $rPr->item(0)->getElementsByTagName('shd');

				if ($shd->length) {
					$shd = $shd->item(0);
					if (!$shd->hasAttribute('w:fill'))
						continue;
					$shdFill = $shd->getAttribute('w:fill');
				}else {
					$shdFill = $this->get_pPr($paragraph);
				}
				if (is_null($shdFill))
					continue;
			}else {
				$shdFill = $this->get_pPr($paragraph);
			}
			if (is_null($shdFill))
				continue;
			$shdFill = ($shdFill == 'FFFFFF' || $shdFill == '') ? OrgFill : $shdFill;

			if ($lastFill != $shdFill) {
				$lastFill = $shdFill;
				if ($shdFill == OrgFill)
					$Key++;
				$returnValue[$Key][$lastFill] = "";
			}else {
				$returnValue[$Key][$lastFill] .= "\n";
				if ($clear
					)$returnValue[$Key][$lastFill] = '';
			}

			foreach ($RList as $K => $R) {
				//$Key = $K;
				$elements = $this->xPath->query(".//w:t|w:br", $R);
				if (!is_null($elements)) {
					foreach ($elements as $element) {
						$clear = false;
						//check fill attr for tras or org text
						switch (strtolower($element->tagName)) {
							case "w:t":
								$returnValue[$Key][$lastFill] .= $element->nodeValue;
								$clear = ($element->nodeValue == LastInstruction);
								break;
							case "w:br":
								$returnValue[$Key][$lastFill] .= "\n";
								break;
						}
					}
					//var_dump($returnValue[$Key]);echo "<BR />";
				}
				#echo "|";
			}
			#echo "#<BR />";
		}
		unset($returnValue[0]);
		return $returnValue;
	}

	function CreateDOCXFile($ArtworkID, $TaskID, $lines=0, $Author="", $Operator="", $Company="") {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$ArtworkName = $row['artworkName'];
		$SourceLangID = $row['sourceLanguageID'];
		$SourceLangFlag = substr($row['SourceLangFlag'], 0, 2);
		$TargetLangID = $row['desiredLanguageID'];
		$TargetLangFlag = substr($row['TargetLangFlag'], 0, 2);

		$exportPara = $this->ExportPara($ArtworkID, $TaskID);

		$paras = count($exportPara);
		if ($paras == 0)
			return false;

		if (!empty($lines)) {
			$counter = $lines;
		} else {
			$counter = $paras;
		}

		$BaseDOCX = RESOURCES . 'Base.docx';
		$this->ReadDOCX($BaseDOCX);

		$helper = new DocX_helper($this->oXML);
		$body = $this->oXML->getElementsByTagName('body')->item(0);

		$query_inCheckRs = sprintf("
	    SELECT content FROM instructions
	    WHERE sourceLangID = %d", $SourceLangID);
		$inCheckRs = mysql_query($query_inCheckRs) or die(mysql_error());
		$totalRows_inCheckRs = mysql_num_rows($inCheckRs);

		$SLID = ($totalRows_inCheckRs > 0) ? $SourceLangID : 1;

		$query_instructionRs = sprintf("SELECT content
				    FROM instructions
				    WHERE sourceLangID = %d",
						$SLID);
		$instructionRs = mysql_query($query_instructionRs) or die(mysql_error());
		$row_instructionRs = mysql_fetch_assoc($instructionRs);

		$p = $helper->CreatePara($row_instructionRs['content'], 'auto');
		$body->appendChild($p);
		$p = $helper->CreateEmptyPara();
		$body->appendChild($p);

		$Required = false;
		foreach ($exportPara as $row) {
			if ($counter == 0)
				break;
			$Trans = $this->TranslateText($row['ParaID'], $TargetLangID, $SourceLangID);
			if ($Trans['LC'] == 0) {
				$Para = str_replace("\r\n", "\n", $row['ParaText']);
				$p = $helper->CreateEmptyPara();
				$body->appendChild($p);
				$p = $helper->CreatePara($Para, OrgFill);
				$body->appendChild($p);
				$p = $helper->CreatePara($Para, TransFill);
				$body->appendChild($p);
				$Required = true;
				$counter--;
			} else {
				//Add Translated to DOCX if not sample DOCX
				if (empty($lines)) {
					$p = $helper->CreateEmptyPara();
					$body->appendChild($p);
					$p = $helper->CreatePara($row['ParaText'], OrgFill);
					$body->appendChild($p);
					$p = $helper->CreatePara($Trans['Para'], TransFill);
					$body->appendChild($p);
					$Required = true;
					$counter--;
				}
			}
		}
		if (!$Required)
			return false;

		$contents = $this->oXML->saveXML();

		$valid = $this->DOCX->setSection('word/document.xml', $contents);
		if ($valid === false)
			return false;
		$File = $ArtworkName . "_" . $SourceLangFlag . "_to_" . $TargetLangFlag;
		if (!empty($lines)) {
			$File .= "_sample";
		}
		$File .= ".DOCX";
		$this->DOCX->reBuild(ROOT . TMP_DIR . $File);
		return $File;
	}

	function export($ArtworkID, $TaskID, $lines=0) {
		return $this->CreateDOCXFile($ArtworkID, $TaskID, $lines);
	}

	function import($ArtworkID, $TaskID, $file, $option=1, $CS=false) {
		if(empty($TaskID)) return false;
		$row = $this->get_task_info($TaskID);
		if($row === false) return false;
		$sourceLanguageID = $row['sourceLanguageID'];
		$TargetLangID = $row['desiredLanguageID'];
		$brandID = $row['brandID'];
		$subjectID = $row['subjectID'];

		$Decoded = $this->ReadDOCX($file);
		if (!$Decoded)
			return false;
		$loose = $CS === true ? 0 : 1;
		$import_id = $this->ImportStart($ArtworkID, $TaskID, "DOCX", $option, $loose);
		$par = $this->getParagraphs();
		$imported = false;
		foreach ($par as $key => $p) {
			if (empty($p[OrgFill]) || empty($p[TransFill]))
				continue;
			$OrgPara = $p[OrgFill];
			$TransPara = $p[TransFill];
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
		if (!$imported)
			return false;
		return $import_id;
	}
}