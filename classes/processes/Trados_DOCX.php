<?php
/**
 * Description of DocX
 *
 * @author MadTechie
 */
require_once(PROCESSES."BaseProcess.php");
class TradosDocXParsa extends Process {
  protected $DOCX;
  protected $oXML;
  protected $xPath;

  /**
   * Read in the DocX file and create a DOM
   * @param string $file
   * @return boolean
   */
  function ReadDOCX($file){
    require_once(ENGINES.'OpenXML/DOCX.php');
    $this->DOCX = new DOCX($file);
    $this->oXML = new DOMDocument('1.0','UTF-8');
    $contents = $this->DOCX->getSection('word/document.xml');
    if(is_null($contents)) return false;
    $contents = $contents->getValue();
    $loaded = $this->oXML->loadXML($contents->asXML(), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
    $this->oXML->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w', 'root');
    $this->xPath = new DOMXPath($this->oXML);
    return true;
  }

  /**
   * Extract Paragraphs
   * @return array
   */
  public function getParagraphs(){
    $body = $this->oXML->getElementsByTagName('body')->item(0);
    $paragraphs = $body->getElementsByTagName('p');

    $returnValue = array();
    $Key = 0;
    foreach($paragraphs as $paragraph) {
      $returnValue[$Key] = "";
      $RList = $paragraph->getElementsByTagName('r');
      $pPr = $paragraph->getElementsByTagName('pPr');
      if(!$pPr->length) continue;

      foreach($RList as $K => $R){
	//$Key = $K;
	$elements = $this->xPath->query(".//w:t|w:br",$R);
	if (!is_null($elements)) {
	  foreach ($elements as $element) {
	    //check fill attr for tras or org text
	    #echo $element->tagName;
	    switch(strtolower($element->tagName)){
	      case "w:t":
		$returnValue[$Key] .= $element->nodeValue;
	      break;
	      case "w:br":
		$returnValue[$Key] .= "\n";
	      break;
	    }
	  }
	}
      }
      $Key++;
    }
    return $returnValue;
  }

  /**
   * Create a DocX file using paragraphs from the database
   * @param int $ArtworkID
   * @param int $TargetLangID
   * @param int $SourceLangID
   * @param string $Author
   * @param string $Operator
   * @param string $Company
   * @param int $lines
   * @return string filename
   */
  function CreateDOCXFile($ArtworkID=0, $TargetLangID=0, $SourceLangID=0, $Author="", $Operator="", $Company="", $lines=0) {
    $BaseDOCX = RESOURCES.'Base.docx';

    if(empty($ArtworkID) || empty($TargetLangID) || empty($SourceLangID)) return false;

    $query_artworkRs = sprintf("SELECT * FROM artworks LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID WHERE artworkID = %d", $ArtworkID);
    $artworkRs = mysql_query($query_artworkRs) or die(mysql_error());
    $row_artworkRs = mysql_fetch_assoc($artworkRs);

    $query_sourceLanRs = sprintf("SELECT flag FROM languages WHERE languageID = %d", $SourceLangID);
    $sourceLanRs = mysql_query($query_sourceLanRs) or die(mysql_error());
    $row_sourceLanRs = mysql_fetch_assoc($sourceLanRs);

    $query_targetLanRs = sprintf("SELECT flag FROM languages WHERE languageID = %d", $TargetLangID);
    $targetLanRs = mysql_query($query_targetLanRs) or die(mysql_error());
    $row_targetLanRs = mysql_fetch_assoc($targetLanRs);

    $exportPara = $this->ExportPara($ArtworkID);
    $Required = false;

    $paras = count($exportPara);

    if(!empty($lines)) {
      $counter = $lines;
    } else {
      $counter = $paras;
    }

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
    
    foreach($exportPara as $row) {
		if($counter==0) break;
		$Trans = $this->TranslateText($row['ParaID'], $TargetLangID, $SourceLangID);
		if($Trans['LC'] == 0){
			$Para = str_replace("\r\n","\n",$row['ParaText']);
			$p = $helper->CreateEmptyPara();
			$body->appendChild($p);
			$p = $helper->CreatePara($Para);
			$body->appendChild($p);
			$Required = true;
			$counter--;
		} else {
			//Add Translated to DOCX if not sample DOCX
			if(empty($lines)) {
				$p = $helper->CreateEmptyPara();
				$body->appendChild($p);
				$p = $helper->CreatePara($row['ParaText']);
				$body->appendChild($p);
				$Required = true;
				$counter--;
			}
		}
    }
	if(!$Required) return true;

    $contents = $this->oXML->saveXML();

    $this->DOCX->setSection('word/document.xml',$contents);

    $File = $row_artworkRs['campaignID'].$row_artworkRs['artworkID']."_".$row_artworkRs['artworkName']."_".substr($row_sourceLanRs['flag'],0,2)."_to_".substr($row_targetLanRs['flag'],0,2);
    if(!empty($lines)) {
	    $File .= "_sample";
    }
    $File .= ".DOCX";
    $this->DOCX->reBuild(ROOT.TMP_DIR.$File);
    return $File;
  }

  /**
   * Create an array of Original and Translated paragraphs
   * @param string $str
   * @return array
   */
  function TradosSplit($str){
  	$regEx = '/\{0>(.+?)<\}\d+\{>(.+?)<0\}/sim';
  	if(!preg_match($regEx,$str)) return false;
    $org = preg_replace($regEx, '\1', $str);
    $trans = preg_replace($regEx, '\2', $str);
    $arr = array($org,$trans);
	if (preg_match('/^(?:\{\d+>|<\d+\}|<\}\d+\{>)$/sim', $str)) {
		$arr = array("","");
	}
    return $arr;
  }

  /**
   *
   * @param int $TaskID
   * @param string $file
   * @return importID
   */
  function import($ArtworkID, $TaskID, $file, $option=1, $CS=true) {
	if(empty($TaskID)) return false;
    $row = $this->get_task_info($TaskID);
	if($row === false) return false;
    $sourceLanguageID = $row['sourceLanguageID'];
    $TargetLangID = $row['desiredLanguageID'];
    $brandID = $row['brandID'];
    $subjectID = $row['subjectID'];

    $Decoded = $this->ReadDOCX($file);
    if($Decoded === false) return false;
	$loose = $CS===true ? 0 : 1 ;
    $import_id = $this->ImportStart($ArtworkID,$TaskID,"DOCX",$option,$loose);
    $par = $this->getParagraphs();
    $imported = false;
    foreach($par as $key=>$p) {
      //Split
      $arr = $this->TradosSplit($p);
      if($arr === false) continue;
      //validate import para
      if(empty($arr[0]) || empty($arr[1])) continue;
      $imported = true;
      $OrgPara = $arr[0];
      $TransPara = $arr[1];

      $source_para_row = $this->ParaExists($OrgPara,$sourceLanguageID,$CS);
      //Get ParaGroup from Database;
      if($source_para_row === false || (empty($option) && $TransPara==$OrgPara)){
	      $this->AddImportRow($import_id,$OrgPara,$TransPara,0);
      }else{
	      $PG = $source_para_row['PG'];
	      $this->AddTranslated($TransPara,$TargetLangID,$PG,$sourceLanguageID,$TaskID,$_SESSION['userID'],PARA_IMPORT,$brandID,$subjectID);
	      $this->AddImportRow($import_id,$OrgPara,$TransPara,1);
      }
    }
    $this->ImportEnd($import_id);
    if($imported === false) return false;
    return $import_id;
  }
}
?>