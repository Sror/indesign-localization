<?php
require_once dirname(__FILE__).'/../../config.php';

require_once(ENGINES . 'OpenXML/DOCX.php');
/**
 * Description of AlterianCM Extractor Engine
 *
 * @author Paul
 */

/**
 * /w:document/w:body/w:tbl/w:tr/w:tc/w:p/w:pPr/w:pStyle[@w:val='FieldValue']/../../w:r/w:t 
 */

define("JPEG_SCALE", 2);
require_once(CLASSES . 'translator.php');
require_once(CLASSES . 'DocInfo.php');
Class AlterianCMEngine extends Translator {
    protected $DOCX;
    protected $oXML;
    protected $xPath;
    
    protected $DocInfo;
    protected $useCache;
    
    protected $link;
    protected $FileFonts = array();
    
	protected $ServerModule = 'C:\Program Files\DocxService';
	private $ServerID = false;
    private $Servers = array(
        "cogent1" => "",
        "cogent2" => "",
        "cogent3" => "",
        "cogent4" => "",
        "cogent5" => "",
    );
    
    protected $Storage = UPLOAD_DIR;

    function __construct() {
        parent::__construct();
        if ($this->ServerID) {
            file_put_contents($this->Storage . "/" . $this->ServerID, "1");
            unlink($this->Storage . "/" . $this->ServerID);
        }

        $this->link = $this->GetDBLink();
        $this->XML = new DOMDocument('1.0', 'UTF-8');
        
        $this->oXML = new DOMDocument('1.0', 'UTF-8');
    }
    
    function IsServerRunning($timeout=10){
		return ($this->getSession()!==false);
    }
	
	function isCached($Filename, $TaskID) {
		return false;
	}

	function CachedTime($Filename, $TaskID=0) {
		return time();
	}

	function EmptyCache($Filename, $TaskID=0) {
		return true;
	}
    
    function GetStorage(){
        return $this->Storage;
    }
    
    public function GetDBLink() {
        return $this->link;
    }
    public function GetFileFonts() {
        return $this->FileFonts;
    }
    
    public function isValidFile($FileName, $FilePath = "") {
        $FilePath = $this->GetStorage();
        return $this->GetFileDetails($FileName, $FilePath);
    }

    public function GetFileDetails($FileName = "", $FilePath = "") {
        $this->ReadDOCX($FilePath.DIRECTORY_SEPARATOR.$FileName);
		
        $this->DocInfo = new DocInfo();
        $this->DocInfo->setName(basename($FileName));
        $this->DocInfo->setPages($this->getPages());
        $this->DocInfo->setWidth(MMtoPX(210)); //210mm
        $this->DocInfo->setHeight(MMtoPX(297)); //297mm
        return true;
    }
    
    public function UpdateArtwork($artworkID = 0, $Extra = array()) {
        if (empty($artworkID)) return false;
        return $this->EditArtworkDetails($artworkID, $Extra);
    }
    
    function getUseCache() {
        return $this->useCache;
    }

    private function setUseCache($useCache) {
        $this->useCache = (bool) $useCache;
    }
    
    public function getDocInfo() {
            return $this->DocInfo;
    }
    public function SetPreviewOutputPath() {
            return null;
    }
    
    function __destruct() {
        if ($this->ServerID) {
            file_put_contents($this->Storage . "/" . $this->ServerID, "0");
            unlink($this->Storage . "/" . $this->ServerID);
        }
    }

    function ReadDOCX($file) {
        require_once(ENGINES . 'OpenXML/DOCX.php');
        $this->DOCX = new DOCX($file);
        $this->oXML = new DOMDocument('1.0', 'UTF-8');
        $contents = $this->DOCX->getSection('word/document.xml');
        if ($contents === false) $contents = $this->DOCX->getSection('word\document.xml');
        if (is_null($contents) || $contents === false) return false;
        $contents = $contents->getValue();
		$contents = $this->cleanup($contents);
        $loaded = $this->oXML->loadXML($contents, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $this->oXML->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w', 'root');
        $this->xPath = new DOMXPath($this->oXML);
        return true;
    }
	
    function getPages(){
        $contents = $this->DOCX->getSection('docProps/app.xml');
        if ($contents === false) $contents = $this->DOCX->getSection('docProps\app.xml');
        if (is_null($contents) || $contents === false) return false;
        $contents = $contents->getValue();
		
		$XMLinfo = new DOMDocument('1.0', 'UTF-8');
        $loaded = $XMLinfo->loadXML($contents->asXML(), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $XMLinfo->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w', 'root');
		
        return $XMLinfo->getElementsByTagName('Pages')->item(0)->nodeValue;
    }
	
    function cleanup(SimpleXMLElement $contents){
		$contents = $contents->asXML();
		//spellStart|End
		//gramStart|End
		$contents = preg_replace('%</w:t>\s*</w:r>\s*(<w:proofErr w:type="(?:spellStart|spellEnd|gramStart|gramEnd)"\s*/>\s*)+<w:r>\s*<w:t[^>]*>%i', '', $contents);
		//<w:lastRenderedPageBreak />
		//$contents = preg_replace('%<w:lastRenderedPageBreak\s*/>%i', '', $contents);
		return $contents;
	}
	
	function loadFile($file){
        $this->DOCX = new DOCX($file);
        $contents = $this->DOCX->getSection('word/document.xml');
        if ($contents === false) $contents = $this->DOCX->getSection('word\document.xml');
        if (is_null($contents) || $contents === false) return false;
        $contents = $contents->getValue();
		$contents = $this->cleanup($contents);
        $loaded = $this->oXML->loadXML($contents, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $this->oXML->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w', 'root');
        $this->xPath = new DOMXPath($this->oXML);
        return true;
    }
    
    function fetchData($body){
       /*
	   $query_array = array(
            '//w:pPr/w:pStyle[@w:val=\'FieldValue\']/../../w:hyperlink/w:r/w:t',
            '//w:pPr/w:divId/../../w:hyperlink/w:r/w:t',
            '//w:pPr/w:pStyle[@w:val=\'FieldValue\']/../../w:r/w:t',
            '//w:pPr/w:divId/../../w:r/w:t',
        );
		//*/
		$query_array = array(
		'//w:tbl/w:tr/w:tc/w:tcPr/w:shd[@w:fill=\'FFFFFF\']/../../w:p/w:r/w:t',
		'//w:tbl/w:tr/w:tc/w:tcPr/w:shd[@w:fill=\'FFFFFF\']/../../w:p/w:hyperlink/w:r/w:t'
		);
		
		
        $elements = $this->xPath->query(implode('|', $query_array),$body);
        return $elements;
    }
    

    function extractData($aID,$file){
        if(empty($aID)) return false;
        $row = $this->get_artwork_info($aID);
        if($row === false) return false;
        $parse_type = $row['parse_type'];
        $SourceLangID = $row['sourceLanguageID'];
        $brandID = $row['brandID'];
        $subjectID = $row['subjectID'];
        $doc_Height = MMtoPX($row['height']);
        $doc_Width = MMtoPX($row['width']);
		$pageCount = $row['pageCount'];

        if(!$SourceLangID) return false;
        

        if(!$this->loadFile($file)) return false;
        

//AddLayer
$LayerID = '1';
$layer = 'Default';
$layer_colour = '000000';
$layer_visible = 1;
$layer_locked = 0;
       
$layer_id = $this->AddLayer($aID,$LayerID,$layer,$layer_colour,$layer_visible,$layer_locked);    		
		
		
//Add Box
$box_name = "None";
#$box_page = $PageID;
#$box_uid = 1;
$box_top = 0;
$box_left = 0;
$box_right = $doc_Width;
$box_bottom = $doc_Height;
$box_type = "TEXT";
$box_angle = 0;
$grouped = false;
		
        //Add Pages
		$links = Array();
		for($page_count=1;$page_count<=$pageCount;$page_count++){
			#$page_count = '1';
			$Preview = 'NoPreview.jpg';
			$page_uid = '';

			$tFile = BareFilename($file);
			$PagePreview = "$tFile-$page_count.jpg";
			copy(RESOURCES.DIRECTORY_SEPARATOR.$Preview, ROOT.PREVIEW_DIR.$PagePreview);
			$PageID = $this->AddPage($aID,$page_count,$PagePreview,$page_uid);
			$BoxID = @$this->InsertBox($box_name,$PageID,$page_count,$box_top,$box_left,$box_right,$box_bottom,$layer_id,$box_type,$box_angle,$grouped);
			
			if($page_count>1){
				$links[$BoxID] = $page_count-1;
			}
			
			if($PageID === false) return false;		
		}
		
		//update linked boxes
		foreach($links as $k=>$v) {
			$this->UpdateLinkedBoxes($aID,$k,$v);
		}
		
        //w:pPr/w:divId
        #$elements = $this->xPath->query("//w:pPr/w:pStyle[@w:val='FieldValue']/../../w:r/w:t",$body);

        $body = $this->oXML->getElementsByTagName('body')->item(0);
        $SG = $this->AddStoryGroup();
        
        $total_word_count = 0;
        $elements = $this->fetchData($body);
        foreach($elements as $element){
            $para = trim($element->nodeValue);
            if(empty($para)) continue;
            $para = $this->PreParsaPara($element->nodeValue);

            $para_row = $this->AddParagraph($para, $SourceLangID, $BoxID, $_SESSION['userID'], PARA_UPLOAD, $brandID, $subjectID, $SG);
            if ($para_row === false) continue;
            $total_word_count += $para_row['Words'];
            $PL = $para_row['PL'];

            #
            $Tag = "[PL:$PL]";

            /*
            var_dump($this->oXML->saveXML($element));
            $element->nodeValue = "[PL:$PL]";
            #
            $SourcePara = $this->GetParaByPL($PL);
            if ($SourcePara === false) continue;
            $SourceParaText = $SourcePara['ParaText'];
            $SourceParaGroup = $SourcePara['ParaGroup'];

            $TMPara = $this->GetTMPara($TaskID, $SourceParaGroup);
            */
            $Tag = $this->PostParsaPara($Tag);
            $element->nodeValue = $Tag;
        }
        
        $contents = $this->oXML->saveXML();
        $valid = $this->DOCX->setSection('word/document.xml', $contents);
        if ($valid === false) return false;
		@mkdir(dirname($file).DIRECTORY_SEPARATOR.'Output'.DIRECTORY_SEPARATOR.basename($file));
		$basepath = dirname($file).DIRECTORY_SEPARATOR.'Output'.DIRECTORY_SEPARATOR.basename($file).DIRECTORY_SEPARATOR.basename($file,'.docx')."_BASE.docx";
        $this->DOCX->reBuild($basepath);
        
        #$elements = $this->xPath->query("/w:document/w:body/w:tbl/w:tr/w:tc/w:p/w:pPr/w:pStyle[@w:val='FieldValue']/../../w:r/w:t", $this->oXML);
        $this->UpdateArtwork($aID, array("wordCount" => $total_word_count));
    }
	
	function serverrequest($Port, $File, $Type="Both"){
		$logFile = "C:\\Program Files\\DocxService\\$Port.txt";
		switch($Type){
			case "docx2pdf":
				$cmds = "docx2pdf ".BareFilename($File)."\n";
			break;
			case "pdf2jpg":
				$cmds = "pdf2jpg ".BareFilename($File)."\n";
			break;
			default:
				$cmds = "docx2pdf ".BareFilename($File)."\n";
				$cmds .= "pdf2jpg ".BareFilename($File)."\n";
			break;
		}
		file_put_contents($logFile,$cmds,FILE_APPEND);
		while(file_exists($logFile)){sleep(1);}
		return true;
	}
	
	public function RebuildFile($ArtworkID, $TaskID=0, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0) {
		$Filename = $this->GetFilenamebyArtwork($ArtworkID);
		if ($Filename === false) return false;
		
		$this->setUseCache($this->GetCacheStatus($ArtworkID, $TaskID));
		
		#switch ($Type) {
			#case "JPG":
			#	return $this->RebuildPreview($ArtworkID, $TaskID, $Page, $Filename, $outputpath, $MaxSize);
			#	break;
			#default:
				return $this->RebuildDOC($ArtworkID, $TaskID, 0, $Filename, $outputpath, $Type);
		#}
		return false;
	}
	
	function RebuildDOC($ArtworkID, $TaskID, $Page, $Filename, $outputpath="", $Type=""){
		require_once(PROCESSES . 'AlterianCMExtractor.php');
		$backref = ($TaskID==0)?new AmendedAlterian():new TranslatedAlterian();
		
		if (empty($ArtworkID)) return false;
        $row = $this->get_artwork_info($ArtworkID);
        if ($row === false) return false;

        $basepath = $this->GetStorage().'Output'.DIRECTORY_SEPARATOR.basename($row['fileName']).DIRECTORY_SEPARATOR.basename($row['fileName'],'.docx')."_BASE.docx";
        //Rebuild
        if(!$this->loadFile($basepath)) return false;
        $body = $this->oXML->getElementsByTagName('body')->item(0);
        $SG = $this->AddStoryGroup();
        
        $elements = $this->fetchData($body);
        foreach($elements as $element){
            $para = trim($element->nodeValue);
            if(!preg_match('%\[PL:(\d+)\]%', $para,$match)) continue;
            $PL = $match[1];
			$Para = $backref->getData($PL,$TaskID);
            
            $element->nodeValue = $Para;
        }
        $contents = $this->oXML->saveXML();
        $valid = $this->DOCX->setSection('word/document.xml', $contents);
        if ($valid === false) return false;
		
		$filename = $row['artworkName'] . "_" . substr($row['flag'], 0, 2) . $backref->getFileSurfix();
		
		$newfile = $this->GetStorage().'Output'.DIRECTORY_SEPARATOR.basename($row['fileName']).DIRECTORY_SEPARATOR.$row['fileName'];
        $this->DOCX->reBuild($newfile);
        if (!file_exists($newfile)) return false;
		
		//Create Previews
		//$outputpath= ROOT.TMP_DIR;
		copy($newfile,$outputpath.basename($newfile));
		
		$this->RebuildPreview($ArtworkID, $TaskID, $Page, $Filename, $outputpath);
		
        return $newfile;
	}
	
	function getSession(){
		foreach($this->Servers as $ServerID => $ServerDetails) {
			//Service running
			$active = (file_exists($this->ServerModule."/".$ServerID.".lck"));
			if(!$active) continue;
			
			//Service in use
			$inuse = (file_exists($this->ServerModule."/".$ServerID.".txt"));
			if($inuse) continue;
			
			$this->ServerID = $ServerID;
			return $ServerID;
		}
		return false;
	}
	
	public function RebuildPreview($ArtworkID, $TaskID=0, $Page, $Filename="", $outputpath="") {
		$Filename = $this->GetFilenamebyArtwork($ArtworkID);
		$path = $this->GetStorage() . $Filename . "/";
		
		if($this->getSession()===false) return false;
		
		$this->serverrequest($this->ServerID, $Filename);
		//Generate JPGs
		$query = sprintf("SELECT pages.Page, pages.PageRef
						FROM pages
						LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
						WHERE pages.ArtworkID = %d
						ORDER BY pages.Page ASC",
						$ArtworkID);

		$result = mysql_query($query) or die(mysql_error());
		$count = mysql_num_rows ( $result );
		while ($row = mysql_fetch_assoc($result)) {
			if($count==1){
				$newFile = OUTPUT_DIR . $Filename . "/" . BareFilename($Filename) . ".jpg";
			}else{
				$newFile = OUTPUT_DIR . $Filename . "/" . BareFilename($Filename) . "-" . ($row['Page'] - 1). ".jpg";
			}
			
			#$PagePreview = ROOT.PREVIEW_DIR.BareFilename($Filename)."-".$row['Page'].".jpg";
			
			if($TaskID>0){
				$PagePreview = $outputpath.BareFilename($Filename)."-".$row['Page']."-".$TaskID.".jpg";
			}else{
				$PagePreview = $outputpath.BareFilename($Filename)."-".$row['Page'].".jpg";
			}
						
			if(file_exists($newFile)){
				if (file_exists($PagePreview)) @unlink($PagePreview);
				rename($newFile, $PagePreview);
			}
		}
		return true;
	}
	
    public function CheckOverflow($ArtworkID, $TaskID=0){
        return null;
    }

    /**
     * Prepare Para for tags and tabs
     *
     * @param string $Para
     * @return string
     */
    public function PreParsaPara($Para) {
        $Keys = array(
            "&amp;" => "&",
            "&hTab;" => "\t",
            "&softReturn;" => "\n",
            "&indentHere;" => " ",
            "&punctSpace;" => " ",
            "&ideographicSpace;" => " ",
            "&ndash;" => "-",
            "&flexSpace;" => " ",
            "&shy;" => "",
            '&discHyphen;' => '',
            "&dcThree;" => "",
            "&lineFeed;" => "\n",
            "&ensp;" => " "
        );
        return str_ireplace(array_keys($Keys), array_values($Keys), $Para);
    }
    public function PostParsaPara($Para) {
        $Keys = array(
            "\t" => "&hTab;",
            "\n" => "&softReturn;",
            "&" => "&amp;"
        );
        $Para = str_replace(array_keys($Keys), array_values($Keys), $Para);
        return $Para;
    }
    
}
