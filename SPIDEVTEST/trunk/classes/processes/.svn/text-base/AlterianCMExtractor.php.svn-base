<?php
/**
 * Description of AlterianCM Extractor Process
 *
 * @author Paul
 */

/**
 * /w:document/w:body/w:tbl/w:tr/w:tc/w:p/w:pPr/w:pStyle[@w:val='FieldValue']/../../w:r/w:t 
 */

define("JPEG_SCALE", 2);

require_once(ENGINES."AlterianCMExtractor.php");
require_once(PROCESSES."BaseProcess.php");
class AlterianCMProcess extends Process {
    protected $AlterianCMEngine;
    protected $XML;
    Protected $link;
    
    protected $DOCX;
    protected $oXML;
    protected $xPath;

    function __construct() {
        $this->AlterianCMEngine = new AlterianCMEngine();
        $this->link = $this->AlterianCMEngine->GetDBLink();
        $this->XML = new DOMDocument('1.0', 'UTF-8');
        
        $this->oXML = new DOMDocument('1.0', 'UTF-8');
    }
    
    function GetPDFDefault() {
        return 0;
    }

    protected $PDFOptions = Array('none');


    function AddPDFOption($Key, $File) {
        $this->PDFOptions[$Key] = $File;
    }

    function GetPDFOptions() {
        return $this->PDFOptions;
    }

    function getPDFOption() {
        return parent::getPDFOption();
    }

    public function UploadFile($aID, $filein) {
		$result = $this->AlterianCMEngine->extractData($aID,$this->AlterianCMEngine->GetStorage().$filein);
		//Previews
		@mkdir(dirname($file).DIRECTORY_SEPARATOR.'Output'.DIRECTORY_SEPARATOR.basename($file).DIRECTORY_SEPARATOR.'Original');
		//exec()
		$this->AlterianCMEngine->RebuildFile($aID, 0, 0, ROOT.PREVIEW_DIR);
		
		//
        return $result;
    }
    
    public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
        if (empty($ArtworkID) || !empty($record_id)) return false;
        $row = $this->get_artwork_info($ArtworkID);
        if ($row === false) return false;
        $filename = $row['artworkName'] . "_" . substr($row['flag'], 0, 2) . ".docx";
        copy($this->AlterianCMEngine->GetStorage() . $row['fileName'], ROOT . TMP_DIR . $filename);
        if (!file_exists(ROOT . TMP_DIR . $fileName)) return false;
        return $filename;
    }
	
    function loadFile($file){
        $this->DOCX = new DOCX($file);
        $contents = $this->DOCX->getSection('word/document.xml');
        if ($contents === false) $contents = $this->DOCX->getSection('word\document.xml');
        if (is_null($contents) || $contents === false) return false;
        $contents = $contents->getValue();
        $loaded = $this->oXML->loadXML($contents->asXML(), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $this->oXML->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w', 'root');
        $this->xPath = new DOMXPath($this->oXML);
        return true;
    }
    
    function fetchData($body){
        $query_array = array(
            '//w:pPr/w:pStyle[@w:val=\'FieldValue\']/../../w:hyperlink/w:r/w:t',
            '//w:pPr/w:divId/../../w:hyperlink/w:r/w:t',
            '//w:pPr/w:pStyle[@w:val=\'FieldValue\']/../../w:r/w:t',
            '//w:pPr/w:divId/../../w:r/w:t',
        );
        $elements = $this->xPath->query(implode('|', $query_array),$body);
        return $elements;
    }
    
}

class OriginalAlterian extends AlterianCMProcess{
    public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
        if (empty($ArtworkID) || !empty($record_id)) return false;
        $row = $this->get_artwork_info($ArtworkID);
        if ($row === false) return false;
        $filename = $row['artworkName'] . ".docx";
        copy($this->AlterianCMEngine->GetStorage() . $row['fileName'], ROOT . TMP_DIR . $filename);
        if (!file_exists(ROOT . TMP_DIR . $fileName)) return false;
        return $filename;
    }
}
class TranslatedAlterian extends AlterianCMProcess{
	public function getData($PL,$TaskID){
		$TransPara = $this->GetTransPara($TaskID,$PL);
		if($TransPara===false){
			$SourcePara = $this->GetParaByPL($PL);
			$Para = $SourcePara['ParaText'];                
		}else{
			$Para = $TransPara;
		}
		$Para = $this->AlterianCMEngine->PostParsaPara($Para);
		return $Para;
	}
	public function getFileSurfix(){
		return  ".docx";
	}
    public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
        return $this->AlterianCMEngine->RebuildDOC($ArtworkID, $TaskID, 0, '', ROOT.TMP_DIR, 'docx');
    }
}

class AmendedAlterian extends AlterianCMProcess {
	public function getData($PL,$Task=0){
		$SourcePara = $this->GetParaByPL($PL);
		$Para = $SourcePara['ParaText'];
		$amended = $this->GetAmendedPara($PL);
		if($amended!==false) $Para = $amended['ParaText'];
		$Para = $this->AlterianCMEngine->PostParsaPara($Para);
		return $Para;
	}
	public function getFileSurfix(){
		return  "_amended.docx";
	}
	public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
		return $this->AlterianCMEngine->RebuildDOC($ArtworkID, $TaskID, 0, '', ROOT.TMP_DIR, 'docx');
	}
}