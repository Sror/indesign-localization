<?php
/**
 * Description of AdobeFLA Extractor Process
 *
 * @author Paul
 */

define("JPEG_SCALE", 2);

require_once(ENGINES."AdobeFLA.php");
require_once(PROCESSES."BaseProcess.php");
class AdobeFLAProcess extends Process {
    protected $AdobeFLAEngine;
    protected $XML;
    Protected $link;
    
    protected $FLA;
    protected $oXML;
    protected $xPath;

    function __construct() {
        $this->AdobeFLAEngine = new AdobeFLAEngine();
        $this->link = $this->AdobeFLAEngine->GetDBLink();
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
		$result = $this->AdobeFLAEngine->extractData($aID,$this->AdobeFLAEngine->GetStorage().$filein);
		//Previews
		@mkdir(dirname($filein).DIRECTORY_SEPARATOR.'Output'.DIRECTORY_SEPARATOR.basename($filein).DIRECTORY_SEPARATOR.'Original');
		//exec()
		$this->AdobeFLAEngine->RebuildFile($aID, 0, 0, ROOT.PREVIEW_DIR);
		
		//
        return $result;
    }
    
    public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
        if (empty($ArtworkID) || !empty($record_id)) return false;
        $row = $this->get_artwork_info($ArtworkID);
        if ($row === false) return false;
        $filename = $row['artworkName'] . "_" . substr($row['flag'], 0, 2) . ".FLA";
        copy($this->AdobeFLAEngine->GetStorage() . $row['fileName'], ROOT . TMP_DIR . $filename);
        if (!file_exists(ROOT . TMP_DIR . $fileName)) return false;
        return $filename;
    }
	/*
    function loadFile($file){
        $this->FLA = new OPENXML($file);
        $contents = $this->FLA->getSection('DOMDocument.xml');
        if (is_null($contents) || $contents === false) return false;
        $contents = $contents->getValue();
        $loaded = $this->oXML->loadXML($contents->asXML(), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $this->xPath = new DOMXPath($this->oXML);
        return true;
    }
    //*/
}

class OriginalAdobeFLA extends AdobeFLAProcess{
    public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
        if (empty($ArtworkID) || !empty($record_id)) return false;
        $row = $this->get_artwork_info($ArtworkID);
        if ($row === false) return false;
        $filename = $row['artworkName'] . ".FLA";
        copy($this->AdobeFLAEngine->GetStorage() . $row['fileName'], ROOT . TMP_DIR . $filename);
        if (!file_exists(ROOT . TMP_DIR . $fileName)) return false;
        return $filename;
    }
}
class TranslatedAdobeFLA extends AdobeFLAProcess{
	public function getData($PL,$TaskID){
		$TransPara = $this->GetTransPara($TaskID,$PL);
		if($TransPara===false){
			$SourcePara = $this->GetParaByPL($PL);
			$Para = $SourcePara['ParaText'];                
		}else{
			$Para = $TransPara;
		}
		$Para = $this->AdobeFLAEngine->PostParsaPara($Para);
		return $Para;
	}
	public function getFileSurfix(){
		return  ".FLA";
	}
    public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
        return $this->AdobeFLAEngine->RebuildFLA($ArtworkID, $TaskID, 0, '', ROOT.TMP_DIR, 'FLA');
    }
}

class AmendedAdobeFLA extends AdobeFLAProcess {
	public function getData($PL,$Task=0){
		$SourcePara = $this->GetParaByPL($PL);
		$Para = $SourcePara['ParaText'];
		$amended = $this->GetAmendedPara($PL);
		if($amended!==false) $Para = $amended['ParaText'];
		$Para = $this->AdobeFLAEngine->PostParsaPara($Para);
		return $Para;
	}
	public function getFileSurfix(){
		return  "_amended.FLA";
	}
	public function DownloadFile($ArtworkID, $TaskID = 0, $record_id = 0, $packed = true) {
		return $this->AdobeFLAEngine->RebuildFLA($ArtworkID, $TaskID, 0, '', ROOT.TMP_DIR, 'FLA');
	}
}