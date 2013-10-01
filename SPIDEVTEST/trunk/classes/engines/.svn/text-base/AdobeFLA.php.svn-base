<?php
require_once dirname(__FILE__).'/../../config.php';

require_once(ENGINES . 'OpenXML/OPENXML.php');
/**
 * Description of AdobeFLA Extractor Engine
 *
 * @author Paul
 */

define("JPEG_SCALE", 2);
require_once(CLASSES . 'translator.php');
require_once(CLASSES . 'DocInfo.php');
Class AdobeFLAEngine extends Translator {
    protected $FLA=null;
    protected $oXML;
    protected $xPath;
    
    protected $DocInfo;
    protected $useCache;
    
    protected $link;
    protected $FileFonts = array();
    
	protected $ServerModule = 'C:\Program Files\FLAService';
	private $ServerID = false;
    private $Servers = array(
        "session1" => "",
        "session2" => "",
        "session3" => "",
        "session4" => "",
        "session5" => "",
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
        $this->loadFile($FilePath.DIRECTORY_SEPARATOR.$FileName,'DOMDocument.xml');
		
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

	//Open FLA
    function ReadFLA($file,$force=false) {
		require_once(ENGINES . 'OpenXML/OPENXML.php');
		if(is_null($this->FLA) || $force) $this->FLA = new OPENXML($file);
        $this->oXML = new DOMDocument('1.0', 'UTF-8');
		return true;
    }
	
	/**
		Load Section
	*/
	//'DOMDocument.xml'
	function loadFile($file,$section){
		if(!$this->ReadFLA($file)) return false;
        $contents = $this->FLA->getSection($section);
        if (is_null($contents) || $contents === false) return false;
        $contents = $contents->getValue();
		$contents = $this->cleanup($contents);
		
		//PATCH Remove Namespace
		$contents = str_ireplace('xmlns="http://ns.adobe.com/xfl/2008/"','',$contents);
		
        $loaded = $this->oXML->loadXML($contents, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        
		//$this->oXML->createElementNS('http://ns.adobe.com/xfl/2008/', 'DOMDocument', 'DOMDocument');

        $this->xPath = new DOMXPath($this->oXML);
		//$this->xPath->registerNamespace('a', "http://ns.adobe.com/xfl/2008/");
        return true;
    }
	
	function saveFile($section, $contents){
		//PATCH Remove Namespace //Just incase
		$contents = str_ireplace('xmlns="http://ns.adobe.com/xfl/2008/"','',$contents);
		
		//PATCH Add Namespace back
		$FindTag = 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
		$contents = str_ireplace($FindTag,$FindTag.' xmlns="http://ns.adobe.com/xfl/2008/"',$contents);
		
		return $this->FLA->setSection($section, $contents);
	}
	
    function getPages(){
		return 1;
        /*
		$contents = $this->FLA->getSection('docProps/app.xml');
        if ($contents === false) $contents = $this->FLA->getSection('docProps\app.xml');
        if (is_null($contents) || $contents === false) return false;
        $contents = $contents->getValue();
		
		$XMLinfo = new DOMDocument('1.0', 'UTF-8');
        $loaded = $XMLinfo->loadXML($contents->asXML(), LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        #$XMLinfo->createElementNS('http://www.w3.org/2001/XMLSchema-instance', '', 'root');
		
        return $XMLinfo->getElementsByTagName('Pages')->item(0)->nodeValue;
		*/
    }
	
    function cleanup(SimpleXMLElement $contents){
		$contents = $contents->asXML();
		#$contents = preg_replace('%</w:t>\s*</w:r>\s*(<w:proofErr w:type="(?:spellStart|spellEnd|gramStart|gramEnd)"\s*/>\s*)+<w:r>\s*<w:t[^>]*>%i', '', $contents);
		//$contents = preg_replace('%<w:lastRenderedPageBreak\s*/>%i', '', $contents);
		return $contents;
	}
    
    function fetchData($body=null){
		// \DOMDocument.xml
		// /DOMDocument/timelines/DOMTimeline/layers/DOMLayer/frames/DOMFrame/elements/DOMStaticText/textRuns/DOMTextRun/characters
		
		// \LIBARY\Tween1.xml
		// /DOMSymbolItem/timeline/DOMTimeline/layers/DOMLayer/frames/DOMFrame/elements/DOMStaticText/textRuns/DOMTextRun/characters
		$this->xPath = new DOMXPath($this->oXML);
		
		$elements = $this->xPath->query('*/DOMTimeline/layers/DOMLayer/frames/DOMFrame/elements/DOMStaticText/textRuns/DOMTextRun/characters',$body);
		return $elements;
    }
    
	function CreatePLs($section, $SourceLangID, $BoxID, $brandID, $subjectID){
		$SG = $this->AddStoryGroup();
		$total_word_count = 0;
		$elements = $this->fetchData();
		foreach($elements as $element){
			$para = trim($element->nodeValue);
			if(empty($para)) continue;

			$para = $this->PreParsaPara($element->nodeValue);

			$para_row = $this->AddParagraph($para, $SourceLangID, $BoxID, $_SESSION['userID'], PARA_UPLOAD, $brandID, $subjectID, $SG);
			if ($para_row === false) continue;
			$total_word_count += $para_row['Words'];
			$PL = $para_row['PL'];

			$Tag = "[PL:$PL]";

			$Tag = $this->PostParsaPara($Tag);
			$element->nodeValue = $Tag;
		}
		
		$contents = $this->oXML->saveXML();
		$valid = $this->saveFile($section, $contents);
		return ($valid===false)?false:$total_word_count;
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

		#if(!$this->ReadFLA($file)) return false;
        if(!$this->loadFile($file,'DOMDocument.xml')) return false;
       

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
		$section = 'DOMDocument.xml';
                $total_word_count = $this->CreatePLs($section, $SourceLangID, $BoxID, $brandID, $subjectID);
		if ($total_word_count === false) return false;

		//
		//Included file
		$List = $this->xPath->query('/DOMDocument/symbols/Include/@href');
		foreach($List as $Item){
			$section = 'LIBRARY/'.$Item->nodeValue;
			$info = explode(".", $section);
			if($info[1] != "xml") continue;
			if(!$this->loadFile($file,$section)) return false;
			
			//Add
			$word_count = $this->CreatePLs($section, $SourceLangID, $BoxID, $brandID, $subjectID);
			if ($word_count === false) return false;
			$total_word_count += $word_count;
		}
		@mkdir(dirname($file).DIRECTORY_SEPARATOR.'Output'.DIRECTORY_SEPARATOR.basename($file));
		$basepath = dirname($file).DIRECTORY_SEPARATOR.'Output'.DIRECTORY_SEPARATOR.basename($file).DIRECTORY_SEPARATOR.basename($file,'.FLA')."_BASE.FLA";
        $this->FLA->reBuild($basepath);
        $this->UpdateArtwork($aID, array("wordCount" => $total_word_count));
    }

	//*
	function serverrequest($Port, $File, $Type="Both"){
		$logFile = "C:\\Program Files\\FLAService\\$Port.txt";
		switch($Type){
			case "FLA2pdf":
				$cmds = "FLA2pdf ".BareFilename($File)."\n";
			break;
			case "pdf2jpg":
				$cmds = "pdf2jpg ".BareFilename($File)."\n";
			break;
			default:
				$cmds = "FLA2pdf ".BareFilename($File)."\n";
				$cmds .= "pdf2jpg ".BareFilename($File)."\n";
			break;
		}
		file_put_contents($logFile,$cmds,FILE_APPEND);
		while(file_exists($logFile)){sleep(1);}
		return true;
	}
	//*/
	public function RebuildFile($ArtworkID, $TaskID=0, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0) {
		$Filename = $this->GetFilenamebyArtwork($ArtworkID);
		if ($Filename === false) return false;
		
		$this->setUseCache($this->GetCacheStatus($ArtworkID, $TaskID));
		
		return $this->RebuildFLA($ArtworkID, $TaskID, 0, $Filename, $outputpath, $Type);
	}
	
	function RebuildFLA($ArtworkID, $TaskID, $Page, $Filename, $outputpath="", $Type=""){
		require_once(PROCESSES . 'AdobeFLA_processor.php');
		$backref = ($TaskID==0)?new AmendedAdobeFLA():new TranslatedAdobeFLA();
		
		if (empty($ArtworkID)) return false;
        $row = $this->get_artwork_info($ArtworkID);
        if ($row === false) return false;

        $basepath = $this->GetStorage().'Output'.DIRECTORY_SEPARATOR.basename($row['fileName']).DIRECTORY_SEPARATOR.basename($row['fileName'],'.FLA')."_BASE.FLA";
        //Rebuild
		//FILES LOOP
		
		if(!$this->loadFile($basepath,'DOMDocument.xml')) return false;
		
		$elements = $this->fetchData();
		foreach($elements as $element){
			$para = trim($element->nodeValue);
			if(!preg_match('%\[PL:(\d+)\]%', $para,$match)) continue;
			$PL = $match[1];
			$Para = $backref->getData($PL,$TaskID);
			
			$element->nodeValue = $Para;
		}
		$contents = $this->oXML->saveXML();
		$valid = $this->saveFile('DOMDocument.xml', $contents);
		if ($valid === false) return false;
		
		//Included file
		$List = $this->xPath->query('/DOMDocument/symbols/Include/@href');
		foreach($List as $Item){
			$section = 'LIBRARY/'.$Item->nodeValue;
			$info = explode(".", $section);
			if($info[1] != "xml") continue;
			if(!$this->loadFile($file,$section)) return false;
			
			$elements = $this->fetchData();
			foreach($elements as $element){
				$para = trim($element->nodeValue);
				if(!preg_match('%\[PL:(\d+)\]%', $para,$match)) continue;
				$PL = $match[1];
				$Para = $backref->getData($PL,$TaskID);
				
				$element->nodeValue = $Para;
			}
			$contents = $this->oXML->saveXML();
			$valid = $this->saveFile($section, $contents);
			if ($valid === false) return false;
		}
		
		//End File Loop
		
		
		$filename = $row['artworkName'] . "_" . substr($row['flag'], 0, 2) . $backref->getFileSurfix();
		
		$newfile = $this->GetStorage().'Output'.DIRECTORY_SEPARATOR.basename($row['fileName']).DIRECTORY_SEPARATOR.$row['fileName'];
        $this->FLA->reBuild($newfile);
        if (!file_exists($newfile)) return false;
		
		//Create Previews
		//$outputpath= ROOT.TMP_DIR;
		copy($newfile,$outputpath.basename($newfile));

		$this->RebuildPreview($ArtworkID, $TaskID, $Page, $Filename, $outputpath);
		
        return $newfile;
	}
	
	function getSession(){
		return true;
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
