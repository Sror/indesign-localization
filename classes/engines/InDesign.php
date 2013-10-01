<?php

/**
 * InDesignEngine, This is used to deconstruct INDD files and put then into Database Entries and rebuild INDD file from those database entries
 * @author Richard Thompson <richard.thompson@sp-int.com>
 * @version 1.0
 * @package PAGLv3
 */
/**
 * INDDEngine Class
 * @example INDD-Example.php This example is in the "examples" subdirectory
 * function test, access is public, will be documented
 * @access public
 * @author Richard Thompson <richard.thompson@sp-int.com>
 * @copyright Copyright (c) 2010, StorePoint International Limited
 * @version -6
 * @param string $Name
 * @param array $ar
 * @return bool
 * @todo make it do something
 * @uses subclass sets a temporary variable
 * @package sample
 */
require_once(CLASSES . 'translator.php');
require_once(CLASSES . 'IDSSoap.php');
require_once(CLASSES . 'DocInfo.php');
require_once(CLASSES."rebuilder/rebuilder2.php");

Class InDesignEngine extends Translator {

	protected $useCache = true;
	public $SourceLang = "EN";
	public $TargetLang = "CN";
	protected $Storage = UPLOAD_DIR;
	protected $OutputPath = OUTPUT_DIR;
	protected $OutputFolder = 'Translated';
	protected $Server = "";
	protected $ServerPort = "";
	protected $PreviewOutputPath = "";
	protected $ServerVersion = 0;
	protected $ServerRunning = false;
	protected $link;
	protected $SystemFontsFamily = array();
	protected $SystemFonts = array();
	protected $FileFonts = array();
	public $IDSR;
	protected $DocInfo;
        private $BASE = 'BASE';

	function __construct() {
		parent::__construct();
		define("WORD_COUNT_MASK", '/\p{L}[\p{L}\p{Mn}\p{Pd}\'\x{2019}]*/u');
		$this->IDSR = new InDesignServerRequest();
		$this->SetPreviewOutputPath(PREVIEW_DIR);
	}

	public function setPDFProfile($pdf_profile) {
		$this->IDSR->setPDFType($pdf_profile);
	}

	function getUseCache() {
		return $this->useCache;
	}

	private function setUseCache($useCache) {
		$this->useCache = (bool) $useCache;
	}

	public function GetDBLink() {
		return $this->link;
	}

	public function IsServerRunning($timeout=10) {
		return $this->IDSR->isRunning();
	}

	public function ServerInfo() {
		return $this->IDSR->ServerInfo();
	}

	public function isValidFile($FileName, $FilePath ="", $FileTypes="JPG.PDF.IDML") {
		return $this->GetFileDetails($FileName, $FilePath,$FileTypes);
	}

	public function getDocInfo() {
		return $this->DocInfo;
	}

	public function GetXMLCommand() {
		return $this->XMLCommand;
	}

	public function GetTranslateCommand() {
		return $this->TranslateCommand;
	}

	public function SetPreviewOutputPath($PreviewOutputPath) {
		$this->PreviewOutputPath = $PreviewOutputPath;
	}

	public function GetFileFonts() {
		return $this->FileFonts;
	}

	public function GetServerVersion() {
		if (empty($this->ServerVersion)) {
			if (!file_exists(INDS_FONTS_LOG))
				$this->ServerInfo();
			$xml = new DomDocument('1.0', 'UTF-8');
			try {
				if (!$xml->load(INDS_FONTS_LOG)) {
					log_error("Failed to load XML file", "LoadInDesignFontXML");
				}
			} catch (Exception $e) {
				log_error($e->getMessage(), "LoadInDesignFontXML");
			}
			$xpath = new DOMXPath($xml);
			$ServerInfos = $xpath->query('//Server/ServerInfo');
			$ServerInfo = $ServerInfos->item(0);
			$this->ServerVersion = $ServerInfo->getElementsByTagName('Version')->item(0)->nodeValue;
		}
		return $this->ServerVersion;
	}

	public function GetStorage() {
		return $this->Storage;
	}
        
        public function getBASE() {
            return $this->BASE;
        }

        /*public function setBASE($BASE) {
            $this->BASE = $BASE;
        }*/
        function setBASE($ArtworkID,$TaskID=0){
            $RecID = $this->getArtworkVersionID($ArtworkID,$TaskID);
            if(is_null($RecID)){
                $this->BASE = 'BASE';
            }else{
                $this->BASE = 'BASE-'.$RecID;
            }
        }
        
        public function getBASEXML() {
            return $this->getBASE().".XML";
        }
        public function getBASEINDD() {
            return $this->getBASE().".INDD";
        }

	public function curl_get_file_contents($URL, $timeout=600) {
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		#curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
		$contents = curl_exec($c);
		curl_close($c);

		if ($contents) {
			return $contents;
		} else {
			return FALSE;
		}
	}

	public function UpdateINDDArtwork($artworkID=0, $Extra=array()) {
		if (empty($artworkID)) return false;
		#$this->FixINDDParaLinks($artworkID);
		return $this->EditArtworkDetails($artworkID, $Extra);
	}

	public function GetFileDetails($FileName="", $FilePath ="", $FileTypes=null) {
		if(is_null($FileTypes)) $FileTypes = "JPG.PDF.IDML";
		$done = $this->IDSR->IDSUpload($this->GetStorage() . $FileName, $FileTypes);
		if ($done !== true) {
			log_error("Error Soap upload", "GetFileDetails");
			return false;
		}

		//get rid of odd characters such as (C) (R)
		#$this->CleanXMLfile($file);
		$file = ($this->OutputPath . $FileName . "/XML/".$this->getBASEXML());
		
		return $this->parsaDocXML($file,$FileName);
	}
	
	protected function parsaDocXML($file,$FileName){
		$this->DocInfo = new DocInfo();
		$XML = new DOMDocument('1.0', 'UTF-8');
		
		if (!file_exists($file)) {
			log_error('XML '.$file." not found", "GetFileDetails");
			return false;
		}
		
		$loaded = $XML->load($file);
		if ($loaded === false) {
			log_error("Error loading XML " . basename($file), "GetFileDetails");
			return false;
		}

		$xpath = new DOMXPath($XML);
		$spreads = $xpath->query('//Document/Spreads/Spread');
		if ($spreads->length == 0) {
			log_error("No spreads found", "GetFileDetails");
			return false;
		}
		$spread = $spreads->item(0);
		$pages = $spread->getElementsByTagName("Pages");
		if ($pages->length == 0) return false;
		$pages = $pages->item(0);
		$pages = $pages->getElementsByTagName("Page");
		if ($pages->length == 0) return false;
		$page = $pages->item(0);
		$page_height = $page->getAttribute('Height');
		$page_width = $page->getAttribute('Width');

		$this->DocInfo->setName($FileName);
		$pages = $xpath->query('//Document/Spreads/Spread/Pages/Page');
		$this->DocInfo->setPages($pages->length);
		$this->DocInfo->setWidth(MMtoPX($page_width));
		$this->DocInfo->setHeight(MMtoPX($page_height));
		$font_details = $xpath->query('//Document/Fonts/Font');
		$this->CheckQFileFonts($font_details);
		return true;
	}

	public function CheckQFileFonts($FontDetails) {
		foreach ($FontDetails as $Font) {
			$Family = $Font->getAttribute('Family');
			if (empty($Family))
				continue;
			$Name = $Font->getAttribute('Name');
			$Style = $Font->getAttribute('Style');
			$Status = $Font->getAttribute('Status');
			$this->FileFonts[] = $this->CheckInstalledFonts(array("Family" => $Family, "Name" => $Name, "Style" => $Style, "Status" => $Status));
		}
		#$this->FileFonts = array_unique($this->FileFonts);
		return $this->FileFonts;
	}

	public function GetIDMLLowPDFSettings() {
		return $this->IDMLLowPDFSettings;
	}

	public function GetIDMLLowPDF() {
		return $this->IDMLLowPDF;
	}

	public function GetIDMLXML() {
		return $this->IDMLXML;
	}

	public function GetPreviewOutputPath() {
		return $this->PreviewOutputPath;
	}

	public function GetPages() {
		return $DocInfo->Pages;
	}

	public function GetName() {
		return $DocInfo->Name;
	}

	public function GetWidth() {
		return $DocInfo->Width;
	}

	public function GetHeight() {
		return $DocInfo->Height;
	}
	
	public function GetOutputFolder() {
		return $this->OutputFolder;
	}
	public function SetOutputFolder($OutputFolder) {
		$this->OutputFolder = $OutputFolder;
	}

	public function GetInstalledFonts() {
		$fonts = array();
		if (!file_exists(INDS_FONTS_LOG))
			$this->ServerInfo();
		$xml = new DomDocument('1.0', 'UTF-8');
		try {
			#file_put_contents(INDS_FONTS_LOG,utf8_encode(file_get_contents(INDS_FONTS_LOG)));
			$xml->load(INDS_FONTS_LOG);
		} catch (Exception $e) {
			log_error($e->getMessage(), "LoadInDesignFontXML");
		}
		$xpath = new DOMXPath($xml);
		$ServerInfos = $xpath->query('//Server/ServerInfo');
		$ServerInfo = $ServerInfos->item(0);
		$this->ServerVersion = $ServerInfo->getElementsByTagName('Version')->item(0)->nodeValue;
		$installed_fonts = $xpath->query('//Server/ServerFonts/Font');
		if ($installed_fonts->length > 0) {
			foreach ($installed_fonts as $installed_font) {
				$family = $installed_font->getAttribute('Family');
				$name = $installed_font->getAttribute('Name');
				$style = $installed_font->getAttribute('Style');
				$fonts[$family][$style] = $name;
			}
		}
		return $fonts;
	}

	public function CheckInstalledFonts(array $Font) {
		if (empty($this->SystemFontsFamily)) {
			$this->SystemFontsFamily = $this->GetInstalledFonts();
		}

		$Installed = 0;
		$Installed = ( isset($this->SystemFontsFamily[$Font['Family']]) && isset($this->SystemFontsFamily[$Font['Family']][$Font['Style']]) );
		if(isset($Font['Status']) && $Font['Status']=="Installed" ) $Installed = 1;
		
		return $this->addFont($Font['Family'], $Font['Name'], $Font['Style'], $Installed);
	}

	#public function CheckFontsHelper($Font, $Family) {
	#	$this->SystemFonts[] = $Font;
	#}

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
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			return $row['id'];
		} else {
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

	public function RebuildFile($ArtworkID, $TaskID=0, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0) {
		$Filename = $this->GetFilenamebyArtwork($ArtworkID);
		if ($Filename === false) return false;
		$this->setBASE($ArtworkID, $TaskID);
		$this->setUseCache($this->GetCacheStatus($ArtworkID, $TaskID));
		switch ($Type) {
			case "JPG":
				return $this->RebuildPreview($ArtworkID, $TaskID, $Page, $Filename, $outputpath, $MaxSize);
				break;
			default:
				return $this->RebuildDOC($ArtworkID, $TaskID, 0, $Filename, $outputpath, $Type);
		}
		return false;
	}

	private function ResetProcessLog($Filename) {
		return @unlink($this->OutputPath.$Filename.'/Progress.log');
	}

	function CacheHandler($details, $TaskID=0) {
		$RealFilename = $details['Filename'];
		$Filename = $this->getBASEINDD();
		$path = $details['path'];
		$type = $details['type'];
		$details['CacheFile'] = null;
		if ($this->useCache) {
			$type .= ".INDD";
			$CacheFile = $this->CacheFile($RealFilename, $TaskID);
			if ($this->isCached($RealFilename, $TaskID)) {
				$Filename = "cache_base-$TaskID.INDD";
				$path = dirname($CacheFile) . "/";
			}
			$details['CacheFile'] = $CacheFile;
		}
		$details['Filename'] = $Filename;
		$details['path'] = $path;
		$details['type'] = $type;
		return $details;
	}

	function MoveCache($Filename, $CacheFile) {
		$File = $this->OutputPath . $Filename . "/".$this->GetOutputFolder()."/$Filename";
		if ((file_exists($File) && is_file($File))) {
			@rename($File, $CacheFile);
		}
	}

	function CacheFile($Filename, $TaskID=0) {
		return  $this->OutputPath . $Filename . "/XML/". $this->CacheFilename($Filename, $TaskID);
	}

	function CacheFilename($Filename, $TaskID=0) {
		return "cache_" . BareFilename($Filename) . "-$TaskID.INDD";
	}

	function isCached($Filename, $TaskID=0) {
		$CacheFile = $this->CacheFile($Filename, $TaskID);
		return (file_exists($CacheFile) && is_file($CacheFile));
	}

	function CachedTime($Filename, $TaskID=0) {
		$CacheFile = $this->CacheFile($Filename, $TaskID);
		if (file_exists($CacheFile) && is_file($CacheFile))
			return filemtime($CacheFile);
		return null;
	}

	function EmptyCache($Filename, $TaskID=0) {
		if ($this->isCached($Filename, $TaskID))
			return unlink($this->CacheFile($Filename, $TaskID));
		return true;
	}

	private function RebuildPreview($ArtworkID, $TaskID=0, $Page=0, $Filename="", $outputpath="", $MaxSize=0) {
		$path = $this->OutputPath . $Filename . "/XML/";
		$RealFilename = $Filename;
		$details = array();
		$details['Filename'] = $Filename;
		$details['path'] = $path;
		$details['type'] = "JPG";
		$details = $this->CacheHandler($details, $TaskID);
		$Filename = $details['Filename'];
		$CacheFile = $details['CacheFile'];
		$type = $details['type'];
		$path = $details['path'];
		$OutSave = $path . "cache_base-$TaskID.INDD";
		$this->SetOutputFolder('Translated/'.$TaskID);
		if (!empty($Page)) {
			$pageRef = $this->GetPageRef($ArtworkID, $Page);
			$file = $this->RebuildXML($ArtworkID, $TaskID, $pageRef, $RealFilename);
			if ($file === false) return false;
			$done = $this->IDSR->IDSTranslate($path . $Filename, $this->GetOutputFolder(), $type, $pageRef, 'XML/' . basename($file), $RealFilename, $OutSave);
			if ($done === false) return false;
			
			$this->MoveCache($RealFilename, $CacheFile);

			$name_suffix = (empty($TaskID)) ? "-$Page.jpg" : "-$Page-$TaskID.jpg";
			$name = $outputpath . BareFilename($RealFilename) . $name_suffix;
			@rename($this->OutputPath . $RealFilename . "/".$this->GetOutputFolder()."/" . BareFilename($RealFilename) . "-$pageRef.jpg", $name);
			return basename($name);
		} else {
			$file = $this->RebuildXML($ArtworkID, $TaskID, 0, $RealFilename);
			if ($file === false) return false;
			$done = $this->IDSR->IDSTranslate($path . $Filename, $this->GetOutputFolder(), $type, 0, 'XML/' . basename($file), $RealFilename, $OutSave);
			if ($done === false) return false;
			
			$this->MoveCache($RealFilename, $CacheFile);
			
			$query = sprintf("SELECT pages.Page, pages.PageRef
							FROM pages
							LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
							WHERE pages.ArtworkID = %d
							ORDER BY pages.Page ASC",
							$ArtworkID);
			$result = mysql_query($query) or die(mysql_error());
			while ($row = mysql_fetch_assoc($result)) {
				$name_suffix = (empty($TaskID)) ? "-" . $row['Page'] . ".jpg" : "-" . $row['Page'] . "-$TaskID.jpg";
				$name = $outputpath . BareFilename($RealFilename) . $name_suffix;
				$newFile = $this->OutputPath . $RealFilename . "/".$this->GetOutputFolder()."/" . BareFilename($RealFilename) . "-" . $row['Page'] . ".jpg";
				if (file_exists($newFile)) {
					if (file_exists($name))
						@unlink($name);
					@rename($newFile, $name);
				}
			}
			return true;
		}
	}

	private function RebuildDOC($ArtworkID, $TaskID=0, $Page=0, $Filename="", $outputpath="", $Type="") {
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$artworkName = $row['artworkName'];
		
		$path = $this->OutputPath . $Filename . "/XML/";
		$RealFilename = $Filename;
		$details = array();
		$RealType = $Type;
		$details['Filename'] = $Filename;
		$details['path'] = $path;
		$details['type'] = $Type;
		$details = $this->CacheHandler($details, $TaskID);
		$Filename = $details['Filename'];
		$CacheFile = $details['CacheFile'];
		$Type = $details['type'];
		$path = $details['path'];
		$OutSave = $path . "cache_base-$TaskID.INDD";
		$this->SetOutputFolder('Translated/'.$TaskID);
		
		$file = $this->RebuildXML($ArtworkID, $TaskID, $Page, $RealFilename);
		if ($file === false) return false;
		$done = $this->IDSR->IDSTranslate($path . $Filename, $this->GetOutputFolder(), $Type, $Page, 'XML/' . basename($file), $RealFilename, $OutSave);
		if ($done === false) return false;
		
		$this->MoveCache($RealFilename, $CacheFile);
		$code = $this->get_country_code($ArtworkID, $TaskID);
		$name = $outputpath . $artworkName . "_$code.$RealType";
		if ($this->useCache) @copy($CacheFile, $name); //Move cache file from cache folder to tmp
		//Move other outputs (PDF) to tmp
		$otherFile = $this->OutputPath . $RealFilename . "/".$this->GetOutputFolder()."/" . BareFilename($RealFilename) . ".$RealType";
		if (file_exists($otherFile) && is_file($otherFile)) @rename($otherFile, $name);

		return basename($name);
	}
	public function INDDPath($file){
		$file = preg_replace('/(\w):\\\\/i', '\\\\\1\\\\', $file);
		$file = implode('/',array_map(rawurlencode,explode('\\',$file)));
		return $file;
	}
	public function WinPath($file){
		$file = urldecode($file);
		$file = preg_replace('%/(\w)/%i', '\1:/', $file);
		$file = str_replace('/','\\',$file);
		return $file;
	}

	private function RebuildXML($ArtworkID, $TaskID=0, $Page=0, $Filename="") {
		$query = sprintf("SELECT campaigns.sourceLanguageID
						FROM artworks
						LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query) or die(mysql_error());
		if (!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$SourceLangID = $row['sourceLanguageID'];
                
		// reset process log
		$this->ResetProcessLog($Filename);
		// read the generated XML
		$XMLFile = $this->OutputPath . $Filename . "/XML/".$this->getBASEXML();
		if (!file_exists($XMLFile) || is_dir($XMLFile)) return false;
		$XML = new DOMDocument('1.0', 'UTF-8');
		$loaded = $XML->load($XMLFile);
		if ($loaded === false) return false;
		$XML->formatOutput = true;
		$XML->preserveWhiteSpace = false;

		$XML = $this->UpdateLayerInfo($XML, $ArtworkID);
		$XML = $this->substitute_font($XML, $ArtworkID, $TaskID);
		$XML = $this->UpdateItemProperties($XML, $ArtworkID, $TaskID);
		$XML = $this->UpdateGeoInfo($XML, $ArtworkID, $TaskID);
		$XML = $this->ReplaceILTags($XML, $ArtworkID, $TaskID);

		//checking if the cache file is available
		$cached = $this->isCached($Filename, $TaskID);
		$item_ids = $this->GetChangedItems($ArtworkID, $TaskID);
		$xpath = new DOMXPath($XML);
		$master_page_items = $xpath->query('//Document/MasterSpreads/Spread/Pages/Page/Item');
		$normal_page_items = $xpath->query('//Document/Spreads/Spread/Pages/Page/Item');
		$all_items = array($master_page_items, $normal_page_items);
		//Loop thought Items Removing unchanged items, log StoryID if changed
		$story_ids = array();
		foreach ($all_items as $items) {
			foreach ($items as $item) {
				$item_id = $item->getAttribute('ID');
				$story_id = $item->getAttribute('parentID');
				if ($cached && $this->useCache && !in_array($item_id,$item_ids)) {
					$item->parentNode->removeChild($item);
				} else {
					$story_ids[] = $story_id;
				}
			}
		}
		// update stories
		$result = $this->get_all_paralinks($ArtworkID);
		$last_file = null;
		$counter=0;
		$Stories = Array();
		foreach($XML->getElementsByTagName('Story') as $storyElement){
			$file = $storyElement->getAttribute('File');
			$story_ref = $storyElement->getAttribute('ID');
			$win_file = $this->WinPath($file);
                        $story_file = $this->OutputPath . $Filename . "/XML/Stories_$TaskID/PAGL-$story_ref.icml";
                        if(!is_dir(dirname($story_file))) mkdir(dirname($story_file));
                        
                        $StoryTaskID = $this->mergedTasks($TaskID,$story_ref);
                        $Tasks[$story_ref] = $StoryTaskID;
                        if($TaskID!=$StoryTaskID){
                            $win_file = $this->OutputPath . $Filename . "/XML/Stories_$StoryTaskID/PAGL-$story_ref.icml";
                            if( (file_exists($win_file) && is_file($win_file))) {
                                copy($win_file, $story_file);
                            }
                        }else{
                            if( !(file_exists($story_file) && is_file($story_file)) && (file_exists($win_file) && is_file($win_file))) {
                                copy($win_file, $story_file);
                            }
                        }
                        $Stories[$story_ref] = $story_file;

			$storyElement->setAttribute('File',$this->INDDPath(realpath($story_file)));
		}
                
                $story = null;
		while($row = mysql_fetch_assoc($result)) {
			$story_ref = $row['StoryRef'];
			$para_ref = $row['ParaRef'];
			$seg_ref = $row['SegRef'];
			$active = $row['active'];
			$type = $row['type'];
			$PL = $row['PL'];
                        if(!isset($Stories[$story_ref])) continue;
                        $TaskID = $Tasks[$story_ref];
			$story_file = $Stories[$story_ref];
			
			if( !(file_exists($story_file) && is_file($story_file)) ) continue;
			
			if($story_file != $last_file){
				if($story instanceof rebuilderControl) $story->save();
				$story = new rebuilderControl($story_file);
				$paras = $story->getParas();
			}
			$last_file = $story_file;
			
			if(!isset($paras[$para_ref]) && !$paras[$para_ref] instanceof Paragraph) continue;
			$paragraph = $paras[$para_ref];
			$segment = $paragraph->getSegment($seg_ref);
			if(!$active){
				$segment->setOmitted(true);
				#$paragraph->removeSegment($seg_ref);
				continue;
			}
			if($type==TYPE_MERGE || $type==TYPE_SPLIT) {
				#$segment = clone $segment;
				#$segment->setNewOrder($row['StoryOrder']);
				#$segment->setOmitted(false);
				#$paragraph->addSegment($segment);
				////----
				#$segment = $story->split($segment,0.5);
				$segment = $story->split($segment);
			}
			$counter++;
			$segment->getParent()->setNewOrder($row['StoryOrder']+($counter*1000));
			
			// compose the segment content
			$SourcePara = $this->GetParaByPL($PL);
			$VerySourcePara = $this->GetParaByPL($PL,false);
			if ($VerySourcePara === false) continue;
			if ($SourcePara === false) continue;
			#if ($SourcePara['ParaText'] == $VerySourcePara['ParaText']) continue;
			$SourceParaText = $SourcePara['ParaText'];
			$SourceParaGroup = $SourcePara['ParaGroup'];
			// check if ignored
			$ignored = $this->FinalCheckParaIgnore($PL,$TaskID);
			if ($ignored) {
				$Para = "";
			} else {
				if (empty($TaskID)) {
					$Para = $SourceParaText;
					$AmendPara = $this->GetAmendedPara($PL,$TaskID);
					if ($AmendPara !== false) {
						$AmendTime = (int) $AmendPara['time'];
						$CacheFileTime = $this->CachedTime($Filename,$TaskID);
						if ($this->useCache && $AmendTime <= $CacheFileTime) continue;
					}
				} else {
					//check if user has picked any translation
					$TransPara = $this->GetTransPara($TaskID,$PL);
					if ($TransPara === false) {
						$TMPara = $this->GetTMPara($TaskID, $SourceParaGroup);
						if ($TMPara === false) {
							//use the origial
							$Para = $SourceParaText;
						} else {
							//user the latest translation
							$Para = $TMPara;
						}
					} else {
						//use user picked translation
						$Para = $TransPara;
					}
				}
			}
			$Para = $this->PostParsaPara($Para);
			// update segment content
			$segment->setContent($Para);
		}
		if($story instanceof rebuilderControl) $story->save();
		
		$XML = $this->XMLCleanUp($XML, $Page);
		$output = $this->OutputPath . $Filename . "/XML/" . BareFilename($Filename) . "-$TaskID.XML";
		
		$XML->save($output);
		//remove redudant whitespaces
		file_put_contents($output, preg_replace('/>(\s)\s*</', '>\1<', file_get_contents($output)));
		if ($this->useCache) $this->ClearItemChanges($ArtworkID, $TaskID, $Page);
		$this->CheckProgress($TaskID);
		return $output;
	}

	private function GetMasterPagesHelper($xpath, $pageNode) {
		$master_id = $pageNode->getAttribute('MasterPageID');
		if ($master_id == 0)
			return array();
		$master_pages = $xpath->query(sprintf('//Document/MasterSpreads/Spread/Pages/Page[@ID=%d]', $master_id));
		$PageArray = array();
		$PageArray[] = $master_pages;
		foreach ($master_pages as $master_page) {
			$PageArray = array_merge($PageArray, $this->GetMasterPagesHelper($xpath, $master_page));
		}
		return $PageArray;
	}

	private function XMLCleanUp($XML, $Page=0) {
		$xpath = new DOMXPath($XML);
		$keep_txt = array();
		$keep_img = array();
		//code below works with page prefix
		$normal_pages = $xpath->query(sprintf('//Document/Spreads/Spread/Pages/Page'));
		$real_normal_pages = array();
		$keep_normal_pages = array();
		foreach ($normal_pages as $normal_page) {
			$normal_page_name = $normal_page->getAttribute('PageName');
			$normal_page_prefix = $normal_page->getAttribute('PagePrefix');
			if(empty($Page)){
				$real_normal_pages[] = $normal_page;
				$keep_normal_pages[] = $normal_page->getAttribute('ID');
			}elseif($Page == $normal_page_prefix . $normal_page_name) {
				$real_normal_pages_node = $normal_page->parentNode;
				$pages = $real_normal_pages_node->getElementsByTagName('Page');
				foreach ($pages as $page) {
					$real_normal_pages[] = $page;
					$keep_normal_pages[] = $page->getAttribute('ID');
				}
			}
		}
		$normal_pages = $real_normal_pages;

		$keep_pages = array();
		$keep_masterpages = array();
		$keep_pages[] = $normal_pages;
		foreach ($normal_pages as $normal_page) {
			$keep_pages = array_merge($keep_pages, $this->GetMasterPagesHelper($xpath, $normal_page));
		}
		
		foreach ($keep_pages as $pages) {
			foreach ($pages as $page) {
				$master_page_id = $page->getAttribute('MasterPageID');
				if ($master_page_id > 0)
					$keep_masterpages[] = $master_page_id;
				$items = $page->getElementsByTagName('Item');
				if ($items->length == 0)
					continue;
				foreach ($items as $item) {
					switch ($item->getAttribute('Type')) {
						case "TEXT_TYPE":
							if (strtolower($item->getAttribute('visible')) != "true")
								continue;
							$keep_txt[] = (int) $item->getAttribute('parentID');
							break;
						case "GRAPHIC_TYPE":
							$keep_img[] = (int) $item->getAttribute('parentID');
							break;
					}
				}
			}
		}
		$keep_txt = array_unique($keep_txt);
		$keep_img = array_unique($keep_img);
		//remove unassociated stories
		$stories = $xpath->query('//Document/Stories/Story');
		foreach ($stories as $story) {
			if (in_array($story->getAttribute('ID'), $keep_txt))
				continue;
			$story->parentNode->removeChild($story);
		}
		//remove unassociated links
		$links = $xpath->query('//Document/Links/Link');
		foreach ($links as $link) {
			if (in_array($link->getAttribute('ObjectID'), $keep_img))
				continue;
			$link->parentNode->removeChild($link);
		}
		//remove unassociated master spread
		$pages = $xpath->query(sprintf('//Document/MasterSpreads/Spread/Pages/Page'));
		$keep_masterspreads = array();
		foreach ($pages as $page) {
			if (in_array($page->getAttribute('ID'),$keep_masterpages)) {
				$spread = $page->parentNode->parentNode;
				$spread_id = $spread->getAttribute('ID');
				$keep_masterspreads[] = $spread_id;
			}
		}
		$master_spreads = $xpath->query(sprintf('//Document/MasterSpreads/Spread'));
		foreach ($master_spreads as $master_spread) {
			$master_spread_id = $master_spread->getAttribute('ID');
			if(!in_array($master_spread_id,$keep_masterspreads)) {
				$master_spread->parentNode->removeChild($master_spread);
			}
		}
		//remove unassociated spread
		$pages = $xpath->query(sprintf('//Document/Spreads/Spread/Pages/Page'));
		$keep_spreads = array();
		foreach ($pages as $page) {
			if (in_array($page->getAttribute('ID'), $keep_normal_pages)) {
				$spread = $page->parentNode->parentNode;
				$spread_id = $spread->getAttribute('ID');
				$keep_spreads[] = $spread_id;
			}
		}
		$spreads = $xpath->query(sprintf('//Document/Spreads/Spread'));
		foreach ($spreads as $spread) {
			$spread_id = $spread->getAttribute('ID');
			if(!in_array($spread_id,$keep_spreads)) {
				$spread->parentNode->removeChild($spread);
			}
		}
		//optimise remaining Page attributes
		$normal_pages = $xpath->query('//Document/Spreads/Spread/Pages/Page');
		$master_pages = $xpath->query('//Document/MasterSpreads/Spread/Pages/Page');
		$array = array($normal_pages, $master_pages);
		foreach ($array as $array_pages) {
			foreach ($array_pages as $array_page) {
				$array_page->removeAttribute('PagePrefix');
				$array_page->removeAttribute('Height');
				$array_page->removeAttribute('Width');
				$array_page->removeAttribute('MasterSpreadID');
				$array_page->removeAttribute('MasterPageID');
				$array_page->removeAttribute('Side');
			}
		}

		return $XML;
	}

	public function CheckOverflow($ArtworkID, $TaskID=0) {
		$query = sprintf("SELECT fileName
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if (!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);
		$INDDFile = $row['fileName'];

		//Reset overflowed boxes
		$update = sprintf("DELETE FROM box_overflows
						WHERE artwork_id = %d
						AND task_id = %d",
						$ArtworkID,
						$TaskID);
		$result = mysql_query($update, $this->link) or die(mysql_error());
                
		//load XML from translated folder
		$XMLFile = $this->OutputPath . $INDDFile . "/".$this->GetOutputFolder()."/BASE.XML";

		$XML = new DOMDocument('1.0', 'UTF-8');
		$XML->load($XMLFile);
		$xpath = new DOMXPath($XML);
		$items = $xpath->query('//Document/Spreads/Spread/Pages/Page/Item');
		//Capture overflowed box_id
		$prep_str = "";
		foreach ($items as $item) {
			$item_id = $item->getAttribute('ID');
			$overflow = $item->getAttribute('overflow');
			#$angle = $item->getAttribute('Angle');
			$query = sprintf("SELECT boxes.uID
							FROM boxes
							LEFT JOIN pages ON boxes.PageID = pages.uID
							WHERE boxes.BoxUID = %d
							AND pages.ArtworkID = %d
							LIMIT 1",
							$item_id,
							$ArtworkID);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			if (!mysql_num_rows($result))
				continue;
			$row = mysql_fetch_assoc($result);
			$BoxID = $row['uID'];
			if ($overflow == "true")
				$prep_str .= "($ArtworkID,$BoxID,$TaskID,1),";
		}
		if (!empty($prep_str)) {
			$update = sprintf("INSERT INTO box_overflows
							(artwork_id, box_id, task_id,overflow)
							VALUES
							%s",
							trim($prep_str, ","));
			$result = mysql_query($update, $this->link) or die(mysql_error());
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
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$xpath = new DOMXPath($XML);
		while ($row = mysql_fetch_assoc($result)) {
			$items = $xpath->query(sprintf('//Document/Spreads/Spread/Pages/Page/Item[@ID=%d]', $row['BoxUID']));
			if ($items->length == 0)
				continue;
			$item = $items->item(0);
			$lock = ($row['lock'] == 1) ? "true" : "false";
			switch ($row['Type']) {
				case "TEXT":
					$resize = ($row['resize'] == 1) ? "TEXTRESIZE" : "NONE";
					break;
				case "PICT":
					$resize = ($row['resize'] == 1) ? "CONTENT_TO_FRAME" : "NONE";
					break;
			}
			$item->setAttribute('locked', $lock);
			$item->setAttribute('Fit', $resize);
			//remove locked item and associated stories
			if ($row['lock'])
				$this->remove_item($XML, $item);
		}
		return $XML;
	}

	function UpdateGeoInfo($XML, $ArtworkID, $TaskID=0) {
		$xpath = new DOMXPath($XML);
		$normal_items = $xpath->query('//Document/Spreads/Spread/Pages/Page/Item');
		$master_items = $xpath->query('//Document/MasterSpreads/Spread/Pages/Page/Item');
		$all_items = array($normal_items, $master_items);
                //$version_id = $this->getArtworkVersionID($ArtworkID,$TaskID);
		foreach ($all_items as $items) {
			foreach ($items as $item) {
				$item_id = $item->getAttribute('ID');
				$visibleBounds = $item->getElementsByTagName('visibleBounds')->item(0);
				$geometricBounds = $item->getElementsByTagName('geometricBounds')->item(0);
				//optimise Item attributes
				$item->removeAttribute('overflow');
				$item->removeAttribute('Grouped');
				$item->removeAttribute('LinkedFrom');
				$item->removeAttribute('LinkedTo');
				$query = sprintf("SELECT box_moves.left, box_moves.right, box_moves.top, box_moves.bottom, box_moves.angle
								FROM box_moves
								LEFT JOIN boxes ON box_moves.box_id = boxes.uID
								WHERE box_moves.artwork_id = %d
								AND box_moves.task_id IN (0,%d)
								AND boxes.BoxUID = %d
								ORDER BY box_moves.task_id DESC
								LIMIT 1",
								$ArtworkID,
								$TaskID,
								$item_id);
				$result = mysql_query($query, $this->link) or die(mysql_error());
				if (mysql_num_rows($result)) {
					//update visibleBounds and geometricBounds
					$row = mysql_fetch_assoc($result);
					$item->setAttribute('Angle', $row['angle']);
					$array = array($visibleBounds, $geometricBounds);
					foreach ($array as $array_item) {
						$array_item->setAttribute('Y1', PXtoMM($row['top']));
						$array_item->setAttribute('X1', PXtoMM($row['left']));
						$array_item->setAttribute('Y2', PXtoMM($row['bottom']));
						$array_item->setAttribute('X2', PXtoMM($row['right']));
					}
				} else {
					// remove unchanged visibleBounds and geometricBounds
					// disbaled due to bug with restore
					#$item->removeAttribute('Angle');
					#$item->removeChild($visibleBounds);
					#$item->removeChild($geometricBounds);
				}
			}
		}
		return $XML;
	}

	function UpdateLayerInfo($XML, $ArtworkID) {
		$query = sprintf("SELECT *
						FROM artwork_layers
						WHERE artwork_id = %d
						ORDER BY ref ASC",
						$ArtworkID);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$xpath = new DOMXPath($XML);
		while ($row = mysql_fetch_assoc($result)) {
			$layer_id = $row['ref'];
			$layer_name = $row['name'];
			$layer_colour = $row['colour'];
			$layer_visible = $row['visible'] ? 'true' : 'false';
			$layer_locked = $row['locked'] ? 'true' : 'false';
			$layers = $xpath->query(sprintf('//Document/Layers/Layer[@ID=%d]', $layer_id));
			if ($layers->length == 0)
				continue;
			$layer = $layers->item(0);
			$layer->setAttribute('Visible', $layer_visible);
			$layer->setAttribute('Locked', $layer_locked);
			$layer->setAttribute('Colour', $this->hex2html($layer_colour));
			if (!$row['visible'] || $row['locked']) {
				$items = $xpath->query(sprintf('//Document/Spreads/Spread/Pages/Page/Item[@LayerID=%d]', $layer_id));
				if ($items->length == 0)
					continue;
				//remove items
				foreach ($items as $item) {
					//except images
					if($item->getAttribute('Type')=="GRAPHIC_TYPE") continue;
					$this->remove_item($XML, $item);
				}
			}
		}
		return $XML;
	}

	protected function remove_item(DOMDocument $XML, DOMElement $item) {
		if (!$XML instanceof DOMDocument || !$item instanceof DOMElement)
			return false;
		$xpath = new DOMXPath($XML);
		$item_type = $item->getAttribute('Type');
		$item_parent_id = $item->getAttribute('parentID');
		//remove associated stories and links
		switch (strtoupper($item_type)) {
			case "TEXT_TYPE":
				$objects = $xpath->query(sprintf('//Document/Stories/Story[@ID=%d]', $item_parent_id));
				break;
			case "GRAPHIC_TYPE":
				$objects = $xpath->query(sprintf('//Document/Links/Link[@ObjectID=%d]', $item_parent_id));
				break;
		}
		if ($objects->length > 0) {
			$object = $objects->item(0);
			$object->parentNode->removeChild($object);
		}
		//remove item itself
		$item->parentNode->removeChild($item);
	}

	function ReplaceILTags($XML, $ArtworkID, $TaskID=0) {
		$xpath = new DOMXPath($XML);
		$links = $xpath->query('//Document/Links/Link');
		foreach ($links as $k => $v) {
			if (!preg_match('%\[IL:(\d+)\]%si', $v->getAttribute('filePath'), $match))
				continue;
			$IL = $match[1];
			$query = sprintf("SELECT images.content
							FROM img_usage
							LEFT JOIN images ON images.id = img_usage.img_id
							LEFT JOIN img_links ON img_links.box_id = img_usage.box_id
							WHERE img_links.id = %d
							AND img_usage.artwork_id = %d
							AND img_usage.task_id IN (0,%d)
							ORDER BY img_usage.task_id DESC, images.time DESC
							LIMIT 1",
							$IL,
							$ArtworkID,
							$TaskID);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			if (mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$img = $row['content'];
			} else {
				//reaplce with the original path
				$query = sprintf("SELECT images.content
								FROM img_links
								LEFT JOIN images ON images.id = img_links.img_id
								LEFT JOIN boxes ON img_links.box_id = boxes.uID
								LEFT JOIN pages ON boxes.PageID = pages.uID
								WHERE img_links.id = %d
								AND pages.ArtworkID = %d
								LIMIT 1",
								$IL,
								$ArtworkID);
				$result = mysql_query($query, $this->link) or die(mysql_error());
				if (mysql_num_rows($result)) {
					$row = mysql_fetch_assoc($result);
					$img = $row['content'];
				} else {
					$img = "";
				}
			}

			//replace with filepath
			$v->setAttribute('filePath', $img);
		}
		return $XML;
	}

	function CleanUpPara($text) {
		$headReg = '/^.*>>/sm';
		$header = "";
		if (preg_match($headReg, $text, $regs)) {
			$header = $regs[0];
			$text = preg_replace($headReg, '', $text);
		}

		$regEx = '/<([^:]+):([^>]+)>(.*?)<\1:>/sim';

		$att = array();
		$value = array();
		$text = preg_replace('/<(ParaStyle:[^>]+)>/sim', '[\1]', $text);

		while (preg_match($regEx, $text, $matches)) {
			$att[$matches[1]] = $matches[2];
			if (strpos($matches[3], "<") === false && strpos($matches[3], ">") === false) {
				ksort($att);
				$value[] = array("TEXT" => $matches[3], "Attributes" => $att);
				$text = preg_replace($regEx, '\3', $text, 1);
				$TagID = (count($value) - 1);
				$text = preg_replace('#' . preg_quote($matches[3], '#') . '#', "TAG[$TagID]", $text, 1);
				$att = array();
			} else {
				$text = preg_replace($regEx, '\3', $text, 1);
			}
		}

		$text = preg_replace('/\[(ParaStyle:[^\]]+)\]/sim', '<\1>', $text);
		preg_match_all('/TAG\[(\d+)\]/s', $text, $tags, PREG_PATTERN_ORDER);
		$tags = $tags[1];
		$value = $this->merge($value);
		foreach ($tags as $TagID) {
			$section_text = "";
			if (isset($value[$TagID])) {
				$section_attr = $value[$TagID]['Attributes'];
				$section_text = $value[$TagID]['TEXT'];
				foreach ($section_attr as $s_key => $s_value) {
					$section_text = "<$s_key:$s_value>$section_text<$s_key:>";
				}
			}
			$text = str_replace("TAG[$TagID]", $section_text, $text);
		}
		return $header . $text;
	}

	function removeAttribute($section_attr) {
		unset($section_attr['cTracking']);
		unset($section_attr['cKerning']);
		unset($section_attr['cLeading']);
		return $section_attr;
	}

	function merge($values) {
		$new_values = array();
		$lastHash = "";
		$lastKey = 0;
		foreach ($values as $TagID => $value) {
			$section_attr = $values[$TagID]['Attributes'];
			$section_text = $values[$TagID]['TEXT'];

			$section_attr = $this->removeAttribute($section_attr);
			$hash = serialize($section_attr);
			if ($hash != $lastHash) {
				$lastHash = $hash;
				$lastKey = $TagID;
				$new_values[$TagID]['Attributes'] = $values[$TagID]['Attributes'];
				$new_values[$TagID]['TEXT'] = $values[$TagID]['TEXT'];
			} else {
				$new_values[$lastKey]['TEXT'] .= $values[$TagID]['TEXT'];
			}
		}

		return $new_values;
	}

	function substitute_font($xml, $artwork_id, $task_id=0) {
		$xpath = new DOMXPath($xml);
		$fonts = $xpath->query('//Document/Fonts/Font');
		foreach ($fonts as $font) {
			$font_family = $font->getAttribute('Family');
			if (empty($font_family)) continue;
			$font_name = $font->getAttribute('Name');
			$font_style = $font->getAttribute('Style');
			$font_status = $font->getAttribute('Status');

			$font_id = $this->get_font_id($font_family, $font_name, $font_style, ENGINE_INDESIGN_ID);

			require_once(CLASSES.'Font_Substitution.php');
			//$level = 'company';
			//$level = 'campaign';
			//$levelID = i.e TaskID
			if(!empty($task_id)){
				$level = 'task';
				$levelID = $task_id;
			}else{
				$level = 'artwork';
				$levelID = $artwork_id;
			}

			$sub_font_info = Font_Substitution::useFont($font_id,$levelID,$level);
			$sub_font_id = $sub_font_info['font'];
			$sub_type = $sub_font_info['sub_type'];
			$substitute = $this->get_font_info($sub_font_id);
			
			if($sub_font_id===false) continue;
			$sub_font_info = $this->get_font_info($sub_font_id);
			$font->setAttribute('Status', "SUBSTITUTED");
			$font->setAttribute('SubstitutedFont', $sub_font_info['name'] );
		}
		return $xml;
	}


	/**
	 * Cleans Para before inserting into database
	 *
	 * @param string $Para
	 * @return string
	 */
	public function ParsaPara($Para) {
		$Keys = array(
			"&discHyphen;" => ""
		);
		$Para = str_ireplace(array_keys($Keys), array_values($Keys), $Para);
		return parent::ParsaPara($Para);
	}

	public function PreParsaStory($Story) {
		$Keys = array(
			"\<" => "&lt;",
			"\>" => "&gt;"
		);
		return str_ireplace(array_keys($Keys), array_values($Keys), $Story);
	}

	public function PostParsaStory($Story) {
		$Keys = array(
			"&lt;" => "\<",
			"&gt;" => "\>"
		);
		return str_ireplace(array_keys($Keys), array_values($Keys), $Story);
	}

	public function PostParsaPara($Para) {
		$Keys = array(
			"<" => "\<",
			">" => "\>"
		);
		return str_ireplace(array_keys($Keys), array_values($Keys), $Para);
	}

	public $colour_array = array(
		"LIGHTBLUE" => "ADD8E6",
		"LIGHT_BLUE" => "ADD8E6",
		"RED" => "FF0000",
		"GREEN" => "00FF00",
		"BLUE" => "0000FF",
		"YELLOW" => "FFFF00",
		"MAGENTA" => "FF00FF",
		"CYAN" => "00FFFF",
		"GRAY" => "736F6E",
		"BLACK" => "000000",
		"ORANGE" => "FF8040",
		"DARKGREEN" => "254117",
		"DARK_GREEN" => "254117",
		"TEAL" => "008080",
		"TAN" => "D2B48C",
		"BROWN" => "804000",
		"VIOLET" => "8D38C9",
		"GOLD" => "D4A017",
		"DARKBLUE" => "0000A0",
		"DARK_BLUE" => "0000A0",
		"PINK" => "FF00FF",
		"LAVENDER" => "E3E4FA",
		"BRICKRED" => "FF0000",
		"BRICK_RED" => "A50021",
		"OLIVE_GREEN" => "667C26",
		"PEACH" => "FF9955",
		"BURGUNDY" => "900020",
		"GRASSGREEN" => "408080",
		"GRASS_GREEN" => "408080",
		"OCHRE" => "CC7722",
		"PURPLE" => "8E35EF",
		"LIGHTGRAY" => "C0C0C0",
		"LIGHT_GRAY" => "C0C0C0",
		"CHARCOAL" => "36454F",
		"GRIDBLUE" => "00008B",
		"GRID_BLUE" => "00008B",
		"GRID_ORANGE" => "FF8C00",
		"FIESTA" => "DC443A",
		"LIGHTOLIVE" => "809050",
		"LIGHT_OLIVE" => "809050",
		"LIPSTICK" => "962C5",
		"CUTETEAL" => "00FFCC",
		"CUTE_TEAL" => "00FFCC",
		"SULPHUR" => "E4BB25",
		"GRIDGREEN" => "006400",
		"GRID_GREEN" => "006400",
	);

	function html2hex($html_colour) {
		if (array_key_exists($html_colour, $this->colour_array)) {
			return $this->colour_array[$html_colour];
		} else {
			$elements = explode(",", $html_colour, 3);
			$hex_colour = "";
			foreach ($elements as $element) {
				$hex_colour .= dechex($element);
			}
			return strtoupper($hex_colour);
		}
	}

	function hex2html($hex_colour) {
		$html_colour = array_search($hex_colour, $this->colour_array);
		if ($html_colour === false) {
			$red = hexdec(substr($hex_colour, 0, 2));
			$green = hexdec(substr($hex_colour, 2, 2));
			$blue = hexdec(substr($hex_colour, 4, 2));
			return "$red,$green,$blue";
		}
		return $html_colour;
	}

	/*	 * ******************************************************************
	  start of template based functions
	 * ****************************************************************** */

	public function RebuildFileTemp($ArtworkID, $RecordID, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0) {
		$query = sprintf("SELECT fileName
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query) or die(mysql_error());
		if (!mysql_num_rows($result))
			return false;
		$row = mysql_fetch_assoc($result);
		switch ($Type) {
			case "JPG":
				return $this->RebuildPreviewTemp($ArtworkID, $RecordID, $Page, $row['fileName'], $outputpath, $MaxSize);
				break;
			default:
				return $this->RebuildDOCTemp($ArtworkID, $RecordID, 0, $row['fileName'], $outputpath, $Type);
		}
		return false;
	}

	public function RebuildPreviewTemp($ArtworkID, $RecordID, $Page=0, $Filename="", $outputpath="", $MaxSize=0) {
		if (!empty($Page)) {
			$pageRef = $this->GetPageRef($ArtworkID, $Page);
			$file = $this->RebuildXMLTemp($ArtworkID, $RecordID, $pageRef, $Filename);
			if ($file === false)
				return false;
			$done = $this->IDSR->IDSTranslate($this->GetStorage() . $Filename, $this->GetOutputFolder(), "JPG", $pageRef, 'XML/' . basename($file));
			if ($done === false)
				return false;
			$name = $outputpath . BareFilename($Filename) . "-$Page.jpg";
			@rename($this->OutputPath . $Filename . "/".$this->GetOutputFolder()."/" . BareFilename($Filename) . "-$pageRef.jpg", $name);
			return basename($name);
		} else {
			$file = $this->RebuildXMLTemp($ArtworkID, $RecordID, 0, $Filename);
			if ($file === false)
				return false;
			$done = $this->IDSR->IDSTranslate($this->GetStorage() . $Filename, $this->GetOutputFolder(), "JPG", 0, 'XML/' . basename($file));
			if ($done === false)
				return false;
			$query = sprintf("SELECT pages.Page, pages.PageRef
							FROM pages
							LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
							WHERE pages.ArtworkID = %d
							ORDER BY pages.Page ASC",
							$ArtworkID);
			$result = mysql_query($query) or die(mysql_error());
			while ($row = mysql_fetch_assoc($result)) {
				$name = $outputpath . BareFilename($Filename) . "-" . $row['Page'] . ".jpg";
				@rename($this->OutputPath . $Filename . "/".$this->GetOutputFolder()."/" . BareFilename($Filename) . "-" . $row['Page'] . ".jpg", $name);
			}
			return true;
		}
	}

	private function RebuildDOCTemp($ArtworkID, $RecordID, $Page=0, $Filename="", $outputpath="", $Type="") {
		$file = $this->RebuildXMLTemp($ArtworkID, $RecordID, $Page, $Filename);
		if ($file === false)
			return false;
		$done = $this->IDSR->IDSTranslate($this->GetStorage() . $Filename, $this->GetOutputFolder(), $Type, $Page, 'XML/' . basename($file));
		if ($done === false)
			return false;
		$name = $outputpath . BareFilename($Filename) . "-$RecordID.$Type";
		@rename($this->OutputPath . $Filename . "/".$this->GetOutputFolder()."/" . BareFilename($Filename) . ".$Type", $name);
		return basename($name);
	}

	function RebuildXMLTemp($ArtworkID, $RecordID, $Page=0, $Filename="") {
		if (empty($RecordID)) return false;
		$query = sprintf("SELECT campaigns.sourceLanguageID
							FROM artworks
							LEFT JOIN campaigns ON campaigns.campaignID = artworks.campaignID
							WHERE artworks.artworkID = %d
							LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query) or die(mysql_error());
		if (!mysql_num_rows($result))
			return false;
		$row = mysql_fetch_assoc($result);
		$SourceLangID = $row['sourceLanguageID'];

		//read the generated XML
		$XMLFile = $this->OutputPath . $Filename . "/XML/".$this->getBASEXML();
		if (!file_exists($XMLFile) || is_dir($XMLFile)) return false;
		$XML = new DOMDocument('1.0', 'UTF-8');
		$loaded = $XML->load($XMLFile);
		if ($loaded === false) return false;
		$XML->formatOutput = true;
		$XML->preserveWhiteSpace = false;

		$XML = $this->UpdateLayerInfo($XML, $ArtworkID);
		$XML = $this->substitute_font($XML, $ArtworkID, 0);
		$XML = $this->UpdateItemProperties($XML, $ArtworkID, 0);
		$XML = $this->UpdateGeoInfo($XML, $ArtworkID, 0);
		$XML = $this->ReplaceILTags($XML, $ArtworkID);

		//stories
		$xpath = new DOMXPath($XML);
		$stories = $xpath->query('//Document/Stories/Story');
		foreach ($stories as $k => $story) {
			$contents = base64_decode($story->nodeValue);
			preg_match_all('%\[PL:(\d+)\]%sim', $contents, $PLID, PREG_PATTERN_ORDER);
			foreach ($PLID[0] as $K => $Tag) {
				$PL = $PLID[1][$K];
				$SourcePara = $this->GetParaByPL($PL);
				if ($SourcePara === false)
					continue;
				$SourceParaText = $SourcePara['ParaText'];
				//check import colname
				$Colname = $this->GetImportColname($ArtworkID, $PL);
				if ($Colname === false) {
					$Para = $SourceParaText;
				} else {
					$ImportData = $this->GetImportData($Colname, $RecordID);
					if ($ImportData === false) {
						$Para = $SourceParaText;
					} else {
						$Para = $ImportData;
					}
				}
				$Para = $this->PostParsaPara($Para);
				$contents = str_ireplace($Tag, $Para, $contents);
			}
			$story->nodeValue = base64_encode($contents);
		}
		//Clear out other pages
		if (!empty($Page))
			$XML = $this->XMLCleanUp($XML, $Page);
		$output = $this->OutputPath . $Filename . "/XML/" . BareFilename($Filename) . ".XML";
		$XML->save($output);
		return $output;
	}

	/*	 * ******************************************************************
	  end of template based functions
	 * ****************************************************************** */
}