<?php
/**
 * IDMLEngine, This is used to deconstruct IDML files and put then into Database Entries and rebuild IDML file from those database entries
 * @author Richard Thompson <richard.thompson@sp-int.com>
 * @version 1.0
 * @package PAGLv3
 */

/**
	 * IDMLEngine Class
	 * @example IDML-Example.php This example is in the "examples" subdirectory
	 * function test, access is public, will be documented
	 * @access public
	 * @author Richard Thompson <richard.thompson@sp-int.com>
	 * @copyright Copyright (c) 210, StorePoint International Limited
	 * @version -6
	 * @param string $Name 
	 * @param array $ar 
	 * @return bool
	 * @todo make it do something
	 * @uses subclass sets a temporary variable 
	 * @package sample
	*/
require_once(CLASSES.'translator.php');
require_once(CLASSES.'DocInfo.php');
require_once(ENGINES.'OpenXML/IDML.php');
Class IDMLEngine extends Translator
{
	public $SourceLang= "EN";
	public $TargetLang= "CN";

	protected $Storage = UPLOAD_DIR;
	protected $Server = "";
	protected $ServerPort = "";
	protected $PreviewOutputPath = "";

	protected $ServerVersion =0;
	protected $ServerRunning = false;


	protected $link;
	protected $SystemFontsFamily = array();
	protected $SystemFonts = array();
	protected $FileFonts = array();

	protected $DocInfo;

	function __construct(){
		parent::__construct();
		define("WORD_COUNT_MASK", '/\p{L}[\p{L}\p{Mn}\p{Pd}\'\x{2019}]*/u');

		//Setup
		$this->SetPreviewOutputPath(PREVIEW_DIR);
	}

	public function IsServerRunning($timeout=10){
		return true;
	}

	public function isValidFile($FileName, $FilePath ="") {
		return $this->GetFileDetails($FileName, $FilePath);
	}

	public function getDocInfo(){
		return $this->DocInfo;
	}

	public function SetPreviewOutputPath($PreviewOutputPath){
		$this->PreviewOutputPath = $PreviewOutputPath;
	}
	
	public function RebuildFile($ArtworkID, $TaskID=0, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0){
		$query = sprintf("SELECT fileName
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		switch($Type) {
			case "JPG":
				return $this->RebuildPreview($ArtworkID,$TaskID,$Page,$row['fileName'],$outputpath,$MaxSize);
			break;
			default:
				return $this->RebuildDOC($ArtworkID,$TaskID,0,$row['fileName'],$outputpath,$Type);
		}
		return false;
	}
	
	private function RebuildPreview($ArtworkID, $TaskID=0, $Page=0, $Filename, $outputpath, $MaxSize=0){
		return true;
	}
	
	private function RebuildDOC($ArtworkID, $TaskID=0, $Page=0, $Filename, $outputpath, $Type){
		switch($Type) {
			case "IDML":
				return $this->RebuildXML($ArtworkID,$TaskID,$Page,$Filename);
			break;
			default:
				return "DUMMY.$Type";
		}
		
	}

	//Translate
	function RebuildXML($ArtworkID, $TaskID=0, $Page=0, $Filename) {
		$query = sprintf("SELECT campaigns.sourceLanguageID
						FROM artworks
						LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$SourceLangID = $row['sourceLanguageID'];
		
		$filein = BareFilename($Filename);
		$input = $this->Storage.$filein.".base";
		if(!file_exists($input) || is_dir($input)) return false;
		$path_parts = pathinfo($input);
		$output = ROOT.TMP_DIR."/".$path_parts['filename']."-$TaskID.IDML";
		
		$IDML = new IDML($input);
		$spreads = $IDML->getSpreads()->getSpreads();
		$boxes = array();
		//prep boxes
		foreach($spreads as $S => $spread){
			foreach($spread->getReferences() as $Ref) {
				$RefID = (string)$Ref->attributes()->ParentStory;
				//exclude linked boxes
				if(!array_key_exists($RefID,$boxes)) {
					$boxes[$RefID] = $PageID;
				}
			}
		}
		if($TaskID) {
			$uWordTotal = 0;
			$mWordTotal = 0;
			$missingWordTotal = 0;
		}
		//start update boxes
		foreach($boxes as $box => $page) {
			#$Dimensions = $spread->getDimensions($box);
			$story = $IDML->getStories()->getStorie($box);
			$contents = $story->getContents();
			foreach($contents as $K=> $paragraph){
				set_time_limit(0);
				$StyleText = (string)$paragraph[0];
				preg_match('%\[PG:(\d+)\]%si',$StyleText,$PGID);
				$PG = $PGID[1];
				//check if PG has been updated at prework
				$PG = $this->GetAmendedPG($ArtworkID,$PG);
				
				if($TaskID) {
					//find para in database
					$query = sprintf("SELECT paraset.*
										FROM paraset
										LEFT JOIN paragraphs ON paraset.ParaID = paragraphs.uID
										WHERE ParaGroup =%d
										AND LangID= %d",
										$PG,
										$SourceLangID);
					$result = mysql_query($query) or die(mysql_error());
					$row = mysql_fetch_assoc($result);
					$SourceParaID = $row['ParaID'];
					
					$result = $this->GetPickedPara($PG, $TaskID, $SourceParaID);
					if($result === false) {
						//find all the available translations
						$query = sprintf("SELECT paragraphs.ParaText, sp.Words as paraWords
											FROM tasks
											LEFT JOIN paragraphs ON paragraphs.LangID = tasks.desiredLanguageID
											LEFT JOIN paragraphs sp ON sp.uID = %d
											LEFT JOIN paraset ON paragraphs.uID = paraset.ParaID
											LEFT JOIN paralinks ON sp.uID = paralinks.ParaID
											LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
											LEFT JOIN pages ON boxes.PageID = pages.uID
											WHERE pages.ArtworkID = %d
											AND paraset.ParaGroup = %d
											AND tasks.taskID = %d
											ORDER BY
											paragraphs.timeRef DESC",
											$SourceParaID,
											$ArtworkID,
											$PG,
											$TaskID);
						$result = mysql_query($query) or die(mysql_error());
						if(mysql_num_rows($result)) {
							//translated so use the latest
							$rRow = mysql_fetch_assoc($result);
							$Para = $rRow['ParaText'];
							$mWordTotal = $mWordTotal + $rRow['paraWords']; //Not translated yet
						} else {
							//Use Org. Text
							$Para = $this->GetParaByPG($PG, $SourceLangID);
							$missingWordTotal = $missingWordTotal + $Para['paraWords'];
							$Para = $Para['Para'];
						}
					} else {
						$Para = $result['ParaText']; //Translated by user
						$uWordTotal = $uWordTotal + $result['paraWords'];
					}
				} else {
					$Para = $this->GetParaByPG($PG, $SourceLangID);
					$Para = $Para['Para'];
				}
				
				$paragraph[0] = $Para;
			}
		}
			
		$IDML->reBuild($output);
		
		if($TaskID) {
			$query = sprintf("UPDATE tasks SET
							userWords = %d, tmWords = %d, missingWords = %d
							WHERE taskID = %d",
							$uWordTotal,
							$mWordTotal,
							$missingWordTotal,
							$TaskID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
		}
		
		return $output;
	}

	public function GetFileFonts(){
		return $this->FileFonts;
	}

	public function GetServerVersion(){
		if(empty($this->ServerVersion))
		{
	  		$this->ServerVersion = 1;
		}
		return $this->ServerVersion;
	}
	
	public function GetStorage(){
		return $this->Storage;
	}

	public function curl_get_file_contents($URL,$timeout=600){
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		#curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
		$contents = curl_exec($c);
		curl_close($c);

		if ($contents)
		{
			return $contents;
		}else{
			return FALSE;	
		}
	}
		
	public function UpdateIDMLArtwork($artworkID=0, $Extra=array()){
		if(empty($artworkID)) return false;
		return $this->EditArtworkDetails($artworkID, $Extra);
	}

	public function GetFileDetails($FileName="", $FilePath =""){
		
		$this->DocInfo = new DocInfo();
		#$path_parts = pathinfo($FileName);
		#$output = $path_parts['dirname']."/".$path_parts['filename']."_NEW.".$path_parts['extension'];
		$IDML = new IDML($this->GetStorage().$FileName);
		$this->DocInfo->setPages(count($IDML->getSpreads()->getSpreads()));
		$this->DocInfo->setName($FileName);
		$this->DocInfo->setWidth(0);
		$this->DocInfo->setHeight(0);
		#$this->CheckQFileFonts($font_details);
		return true;
	}

	public function CheckQFileFonts($FontDetails){
		foreach($FontDetails as $Font) {
			$Family = $Font->getAttribute('Family');
			if(empty($Family)) continue;
			$Name = $Font->getAttribute('Name');
			$Style = $Font->getAttribute('Style');
			$Status = $Font->getAttribute('Status');
			if($Status=="SUBSTITUTED") {
				$Name = "";
				$Style = "";
			}
			$this->FileFonts[] = $this->CheckInstalledFonts(array("Family"=>$Family,"Name"=>$Name,"Style"=>$Style));
		}
		#$this->FileFonts = array_unique($this->FileFonts);
		return $this->FileFonts;
	}

	public function GetIDMLLowPDFSettings(){
		return $this->IDMLLowPDFSettings;
	}
	
	public function GetIDMLLowPDF(){
		return $this->IDMLLowPDF;
	}
	
	public function GetIDMLXML(){
		return $this->IDMLXML;
	}
	
	public function GetPreviewOutputPath(){
		return $this->PreviewOutputPath;
	}
	
	public function GetPages(){
		return $DocInfo->Pages;
	}

	public function GetName(){
		return $DocInfo->Name;
	}

	public function GetWidth(){
		return $DocInfo->Width;
	}

	public function GetHeight(){
		return $DocInfo->Height;
	}
	
	public function GetInstalledFonts(){
		$fonts = array();
		if(!file_exists(INDS_FONTS_LOG)) $this->IDSR->ServerInfo();
		$xml = new DomDocument('1.0','UTF-8');
		try {
			#file_put_contents(INDS_FONTS_LOG,utf8_encode(file_get_contents(INDS_FONTS_LOG)));
			$xml->load(INDS_FONTS_LOG);
		} catch(Exception $e) {
			log_error($e->getMessage(),"LoadInDesignFontXML");
		}
		$installed_fonts = $xml->getElementsByTagName('Font');
		if($installed_fonts->length>0) {
			foreach($installed_fonts as $installed_font) {
				$family = $installed_font->getAttribute('Family');
				$name = $installed_font->getAttribute('Name');
				$style = $installed_font->getAttribute('Style');
				$fonts[$family][$style] = $name;
			}
		}
		return $fonts;
	}

	public function CheckInstalledFonts(array $Font){
		if(empty($this->SystemFontsFamily) || empty($this->SystemFonts))
		{
			$this->SystemFontsFamily = $this->GetInstalledFonts();
			array_walk_recursive($this->SystemFontsFamily, array($this,"CheckFontsHelper"));
		}
		
		if(in_array($Font['Name'],$this->SystemFonts)){
			return $this->addFont($Font['Family'],$Font['Name'],$Font['Style'],1);
		} else {
			return $this->addFont($Font['Family'],$Font['Name'],$Font['Style'],0);
		}
	}
	
	public function CheckFontsHelper($Font, $Family){
		$this->SystemFonts[]= $Font;
	}
	
	private function addFont($family, $font, $style, $installed=0) {
		//select ID FROM table fonts
		$query = sprintf("SELECT id
							FROM fonts
							WHERE family = '%s'
							AND name = '%s'
							AND style = '%s'
							AND engine_id = %d
							LIMIT 1",
							mysql_real_escape_string($family),
							mysql_real_escape_string($font),
							mysql_real_escape_string($style),
							ENGINE_INDESIGN_ID);
		$result = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			return $row['id'];
		}else{
			$update = sprintf("INSERT INTO fonts
								(family, name, style, engine_id, installed)
								VALUES
								('%s', '%s', '%s', %d, %d)",
								mysql_real_escape_string($family),
								mysql_real_escape_string($font),
								mysql_real_escape_string($style),
								ENGINE_INDESIGN_ID,
								$installed);
			$result = mysql_query($update) or die(mysql_error());
			return mysql_insert_id();
		}
	}
	
	public function CheckOverflow($ArtworkID, $TaskID=0) {
		$query = sprintf("SELECT fileName
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$INDDFile = $row['fileName'];
		
		//Reset overflowed boxes
		$update = sprintf("DELETE FROM box_overflows
						WHERE artwork_id = %d
						AND task_id = %d",
						$ArtworkID,
						$TaskID);
		$result = mysql_query($update,$this->link) or die(mysql_error());
		
		//load XML from translated folder
		$XMLFile = OUTPUT_DIR.$INDDFile."/Translated/BASE.XML";
		
		$XML = new DOMDocument('1.0','UTF-8');
		$XML->load($XMLFile);
		$xpath = new DOMXPath($XML);
		$items = $xpath->query('//Document/Spreads/Spread/Pages/Page/Item[@overflow="true"]');
		//Capture overflowed box_id
		foreach($items as $item) {
			$item_id = $item->getAttribute('ID');
			$query = sprintf("SELECT boxes.uID
							FROM boxes
							LEFT JOIN pages ON boxes.PageID = pages.uID
							WHERE boxes.BoxUID = %d
							AND pages.ArtworkID = %d",
							$item_id,
							$ArtworkID);
			$result = mysql_query($query,$this->link) or die(mysql_error());
			$found = mysql_num_rows($result);
			if($found) {
				$row = mysql_fetch_assoc($result);
				$prep_str .= "($ArtworkID,{$row['uID']},$TaskID,1),";
			}
		}
		
		if(!empty($prep_str)) {
			$update = sprintf("INSERT INTO box_overflows
							(artwork_id, box_id, task_id,overflow)
							VALUES
							%s",
							trim($prep_str,","));
			$result = mysql_query($update,$this->link) or die(mysql_error());
		}
	}
	
	function UpdateItemProperties($XML, $ArtworkID, $TaskID=0) {
		$query = sprintf("SELECT box_properties.lock, box_properties.resize,
						boxes.BoxUID, boxes.Type
						FROM box_properties
						LEFT JOIN boxes ON boxes.uID = box_properties.box_id
						WHERE box_properties.artwork_id = %d
						AND box_properties.task_id IN (0,%d)
						ORDER BY box_properties.box_id ASC, box_properties.task_id ASC",
						$ArtworkID,
						$TaskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		$xpath = new DOMXPath($XML);
		while($row = mysql_fetch_assoc($result)) {
			$query = sprintf('//Document/Spreads/Spread/Pages/Page/Item[@ID=%d]',$row['BoxUID']);
			$results = $xpath->query($query);
			if($results->length==0) continue;
			$item = $results->item(0);
			$lock = ($row['lock']==1) ? "true" : "false";
			$resize = ($row['resize']==1) ? "CONTENT_TO_FRAME" : "NONE";
			$item->setAttribute('locked',$lock);
			$item->setAttribute('Fit',$resize);
		}
		return $XML;
	}
	
	function UpdateGeoInfo($XML, $ArtworkID, $TaskID=0) {
		$query=sprintf("SELECT boxes.Name, boxes.BoxUID,
						box_moves.left, box_moves.right, box_moves.top, box_moves.bottom
						FROM box_moves
						LEFT JOIN boxes ON box_moves.box_id = boxes.uID
						WHERE box_moves.artwork_id = %d
						AND box_moves.task_id IN (0,%d)
						ORDER BY box_moves.task_id ASC",
						$ArtworkID,
						$TaskID);
		$result = mysql_query($query,$this->link) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$box_id = $row['BoxUID'];
			//Update Both visibleBounds && geometricBounds
			//Note: visibleBounds control the movment, but geometricBounds is updated for the standards
			$xpath = new DOMXPath($XML);
			$query = sprintf('//Document/Spreads/Spread/Pages/Page/Item[@ID=%d]',$box_id);
			$results = $xpath->query($query);
			if($results->length==0) continue;
			$item = $results->item(0);
			$items = array();
			$visibleBounds = $item->getElementsByTagName('visibleBounds');
			$geometricBounds = $item->getElementsByTagName('geometricBounds');
			$items[] = $visibleBounds->item(0);
			$items[] = $geometricBounds->item(0);
			foreach($items as $item) {
				$item->setAttribute('Y1',PXtoMM($row['top']));
				$item->setAttribute('X1',PXtoMM($row['left']));
				$item->setAttribute('Y2',PXtoMM($row['bottom']));
				$item->setAttribute('X2',PXtoMM($row['right']));
			}
		}
		return $XML;
	}
}
?>