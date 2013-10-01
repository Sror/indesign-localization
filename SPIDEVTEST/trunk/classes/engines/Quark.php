<?php

/**
 * QuarkEngine, This is used to deconstruct Quark files and put then into Database Entries and rebuild Quark file from those database entries
 * @author Richard Thompson <richard.thompson@sp-int.com>
 * @version 1.0
 * @package PAGLv3
 */
/**
 * QuarkEngine Class
 * @example QE-Example.php This example is in the "examples" subdirectory
 * function test, access is public, will be documented
 * @access public
 * @author Richard Thompson <richard.thompson@sp-int.com>
 * @copyright Copyright (c) 2008, StorePoint International Limited
 * @version -6
 * @param string $Name
 * @param array $ar
 * @return bool
 * @todo make it do something
 * @uses subclass sets a temporary variable
 * @package sample
 */
define("JPEG_SCALE", 2);
require_once(CLASSES . 'translator.php');
require_once(CLASSES . 'DocInfo.php');
require_once(CLASSES."QXP_XML_DOM.php");

Class QuarkEngine extends Translator {

	protected $useCache= true;
	public $SourceLang = "EN";
	public $TargetLang = "CN";
	private $ServerID = false;
	private $Servers = array(
		"QXPS_SESSION_DEFAULT_1" => array("URL" => HOST_PATH, "PORT" => PORT_NO),
		"QXPS_SESSION_DEFAULT_2" => array("URL" => HOST_PATH, "PORT" => PORT_NO),
		"QXPS_SESSION_DEFAULT_3" => array("URL" => HOST_PATH, "PORT" => PORT_NO),
		"QXPS_SESSION_DEFAULT_4" => array("URL" => HOST_PATH, "PORT" => PORT_NO),
		"QXPS_SESSION_DEFAULT_5" => array("URL" => HOST_PATH, "PORT" => PORT_NO),
	);
	protected $Storage = "";
	protected $Server = "";
	protected $ServerPort = 0;
	protected $PreviewOutputPath = "";
	protected $ServerVersion = 0;
	protected $DocVersion = 7;
	protected $ServerRunning = false;
	protected $QuarkJPG = "";
	protected $QuarkXML = "";
	protected $QuarkLowPDF = "";
	protected $QuarkLowPDFSettings = "?lowresolution=1&colorimagedownsample=72&colorcompression=0&spreads=1";
	//?lowresolution=1colorimagedownsample=72&colorcompression=0&fontdownload=0&spreads=1&title=MyTitle&subject=MySubject&author=MyAuthor
	protected $QuarkHighPDF = "";
	protected $QuarkHighPDFSettings = "?colorcompression=0&spreads=1";
	protected $QuarkQXD = "";
	protected $link;
	protected $SystemFontsFamily = array();
	protected $SystemFonts = array();
	protected $FileFonts = array();
	private $PDFType;

	function getUseCache(){
		return $this->useCache;
	}
	
	private function setUseCache($useCache){
		$this->useCache = (bool)$useCache;
	}
	
	public function GetDBLink() {
		return $this->link;
	}

	//DocInfo
	protected $DocInfo;
	/*
	  public $Pages = 0;
	  public $Name = "";
	  public $Width = 0;
	  public $Height = 0;
	  public $Fonts = array();
	 */
	private $ServerInfocache = "";

	function __construct() {
		parent::__construct();
		define("WORD_COUNT_MASK", '/\p{L}[\p{L}\p{Mn}\p{Pd}\'\x{2019}]*/u');

		//Setup
		$this->SetPreviewOutputPath(PREVIEW_DIR);
		$this->SetupQuark(HOST_PATH, PORT_NO, UPLOAD_DIR);
		$this->QuarkJPG = $this->Server . ":" . $this->ServerPort . "/jpeg/";
		$this->QuarkXML = $this->Server . ":" . $this->ServerPort . "/xml/";
		$this->QuarkLowPDF = $this->Server . ":" . $this->ServerPort . "/screenpdf/";
		$this->QuarkHighPDF = $this->Server . ":" . $this->ServerPort . "/pdf/";
		$this->QuarkQXD = $this->Server . ":" . $this->ServerPort . "/qxpdoc/";
	}

	function __destruct() {
		if ($this->ServerID) {
			file_put_contents($this->Storage . "/" . $this->ServerID, "0");
			unlink($this->Storage . "/" . $this->ServerID);
		}
	}

	public function setPDFProfile($pdf_profile) {
		$this->PDFType = $pdf_profile;
	}
	public function getPDFProfile() {
		return $this->PDFType;
	}

	public function setDocVersion($DocVersion) {
		$this->DocVersion = $DocVersion;
	}

	public function getDocVersion() {
		return $this->DocVersion;
	}

	public function IsServerRunning($timeout=10) {
		if ($this->ServerPort == 0)
			return false;
		//if its found running then don't re-test during the same class call
		#if($this->ServerRunning) return true;
		$path = $this->Server . ":" . $this->ServerPort;
		$Details = $this->curl_get_file_contents($path, $timeout);
		return ($Details == "<HTML><BODY>QuarkXPress Server Running</BODY></HTML>");
	}

	public function SetupQuark($Server="http://localhost", $ServerPort=8080, $Storage="") {
		$this->Storage = $Storage;
		foreach ($this->Servers as $ServerID => $ServerDetails) {
			if (file_exists($this->Storage . "/" . $ServerID)) {
				$use = file_get_contents($this->Storage . "/" . $ServerID);
			} else {
				$use = "0";
			}
			if ($use == "0") {
				$this->ServerID = $ServerID;
				$this->Server = $this->Servers[$this->ServerID]['URL'];
				$this->ServerPort = (int) $this->Servers[$this->ServerID]['PORT'];
				$this->ServerRunning = true;
				if ($this->ServerRunning !== false)
					break;
			}
		}
		if ($this->ServerID)
			file_put_contents($this->Storage . "/" . $this->ServerID, "1");
	}

	public function getDocInfo() {
		return $this->DocInfo;
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
			return false;
		}
	}

	public function isValidFile($FileName, $FilePath ="") {
		return $this->GetFileDetails($FileName, $FilePath);
	}

	public function UpdateQuarkArtwork($artworkID=0, $Extra=array()) {
		if (empty($artworkID))
			return false;
		return $this->EditArtworkDetails($artworkID, $Extra);
	}

	public function GetFileDetails($FileName="", $FilePath ="") {
		$uFileName = urlencode($FileName);
		$FilePath = trim($FilePath, "/");
		$path = $this->Server . ":" . $this->ServerPort . "/getdocinfo" . $FilePath . "/" . $uFileName;
		$XMLData = @$this->curl_get_file_contents($path);
		if(empty($XMLData) || preg_match('/^Error/i',$XMLData)) {
			log_error("$XMLData $path","QXPS");
			return false;
		}
		$regs = array();
		$this->DocInfo = new DocInfo();

		$XML = new DOMDocument('1.0', 'UTF-8');
		$XML->loadXML($XMLData);
		$xPath = new DOMXpath($XML);
		$projinfo = $xPath->query("//PROJINFO")->item(0);
		$name = $projinfo->getElementsByTagName('NAME')->item(0);
		$layout = $projinfo->getElementsByTagName('LAYOUT')->item(0);
		$pages = $layout->getElementsByTagName('PAGES')->item(0);
		$properties = $layout->getElementsByTagName('PAGEPROPERTIES')->item(0);
		$width = $properties->getElementsByTagName('WIDTH')->item(0);
		$height = $properties->getElementsByTagName('LENGTH')->item(0);

		$this->DocInfo->setName($name->nodeValue);
		$this->DocInfo->setPages($pages->nodeValue);
		$this->DocInfo->setWidth($width->nodeValue);
		$this->DocInfo->setHeight($height->nodeValue);
		$this->CheckQFileFonts($XML);
		return true;
	}

	public function CheckQFileFonts($XML) {
		$xPath = new DOMXpath($XML);
		$fonts = $xPath->query("//PROJINFO/FONTUSAGE/FONT/NAME");
		foreach ($fonts as $font) {
			$this->FileFonts[] = $this->CheckInstalledFonts($font->nodeValue);
		}
		#$this->FileFonts = array_unique($this->FileFonts);
		return $this->FileFonts;
	}

	public function GetQuarkLowPDFSettings() {
		return $this->QuarkLowPDFSettings;
	}

	public function GetQuarkLowPDF() {
		return $this->QuarkLowPDF;
	}

	public function GetQuarkHighPDFSettings() {
		return $this->QuarkHighPDFSettings;
	}

	public function GetQuarkHighPDF() {
		return $this->QuarkHighPDF;
	}

	public function GetStorage() {
		return $this->Storage;
	}

	public function GetQuarkXML() {
		return $this->QuarkXML;
	}

	public function GetFileFonts() {
		return $this->FileFonts;
	}

	public function GetPreviewOutputPath() {
		return $this->PreviewOutputPath;
	}

	public function SetPreviewOutputPath($PreviewOutputPath) {
		$this->PreviewOutputPath = $PreviewOutputPath;
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

	public function GetQuarkURL($File, $FileName="") {
		$HighRes = (bool)$this->getPDFProfile();//0=low //1=high
		$modify = (!empty($FileName)) ? "&modify=file:$FileName" : "";
		if ($HighRes) {
			return $this->QuarkHighPDF . urlencode($File) . $this->GetQuarkHighPDFSettings() . $modify;
		} else {
			return $this->QuarkLowPDF . urlencode($File) . $this->GetQuarkLowPDFSettings() . $modify;
		}
	}

	public function GetInstalledFonts() {
		$Fonts = array();
		$XMLData = $this->ServerInfo();
		$XML = new DOMDocument('1.0', 'UTF-8');
		$XML->loadXML($XMLData);
		$xPath = new DOMXpath($XML);
		$fonts = $xPath->query("//SERVERINFO/INSTALLEDFONTS/FONT");
		foreach ($fonts as $font) {
			$font_family = $font->getElementsByTagName('MENUNAME')->item(0);
			$font_fullnames = $font->getElementsByTagName('FULLNAME');
			foreach ($font_fullnames as $font_fullname) {
				$Fonts[$font_family->nodeValue][] = $font_fullname->nodeValue;
			}
		}
		return $Fonts;
	}

	public function CheckInstalledFonts($Font) {
		if (empty($this->SystemFontsFamily) || empty($this->SystemFonts)) {
			$this->SystemFontsFamily = $this->GetInstalledFonts();
			array_walk_recursive($this->SystemFontsFamily, array($this, "CheckFontsHelper"));
		}

		if (in_array($Font, $this->SystemFonts)) {
			return $this->addFont($Font, 1);
		} else {
			return $this->addFont($Font, 0);
		}
	}

	public function CheckFontsHelper($Font, $Family) {
		$this->SystemFonts[] = $Font;
	}

	private function addFont($font, $installed=0) {
		//select ID FROM table fonts
		$query = sprintf("SELECT id
							FROM fonts
							WHERE name = '%s'
							AND engine_id = %d
							LIMIT 1",
						mysql_real_escape_string($font),
						ENGINE_QUARK_ID);
		$result = mysql_query($query) or die(mysql_error());
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			return $row['id'];
		} else {
			$update = sprintf("INSERT INTO fonts
								(family, name, style, engine_id, installed)
								VALUES
								('', '%s', '', %d, %d)",
							mysql_real_escape_string($font),
							ENGINE_QUARK_ID,
							$installed);
			$result = mysql_query($update) or die(mysql_error());
			return mysql_insert_id();
		}
	}

	public function GetServerVersion() {
		if (empty($this->ServerVersion)) {
			$XMLData = $this->ServerInfo();
			if ($XMLData === false)
				return "-";
			$XML = new DOMDocument('1.0', 'UTF-8');
			$XML->loadXML($XMLData);
			$xPath = new DOMXpath($XML);
			$this->ServerVersion = $xPath->query("//SERVERINFO/VERSIONINFO/VERSIONSTR")->item(0)->nodeValue;
		}
		return $this->ServerVersion;
	}

	public function ServerInfo() {
		if (empty($this->ServerInfocache)) {
			$this->ServerInfocache = @$this->curl_get_file_contents($this->Server . ":" . $this->ServerPort . "/getserverinfo");
		}
		return $this->ServerInfocache;
	}

	public function RebuildFile($ArtworkID, $TaskID=0, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0) {
		$Filename = $this->GetFilenamebyArtwork($ArtworkID);
		if($Filename === false) return false;
		$this->setUseCache($this->GetCacheStatus($ArtworkID,$TaskID));
		switch ($Type) {
			case "JPG":
				return $this->RebuildPreview($ArtworkID,$TaskID,$Page,$Filename,$outputpath,$MaxSize);
				break;
			default:
				return $this->RebuildDOC($ArtworkID,$TaskID, 0,$Filename,$outputpath,$Type);
		}
		return false;
	}

	function CacheHandler($Filename, $TaskID=0) {
		if($this->useCache && $this->isCached($Filename,$TaskID)) $Filename = $this->CacheFilename($Filename,$TaskID);
		return $Filename;
	}

	function CacheFile($Filename, $TaskID=0) {
		return $this->Storage.$this->CacheFilename($Filename,$TaskID);
	}

	function CacheFilename($Filename, $TaskID=0) {
		return "cache_".BareFilename($Filename)."-$TaskID.QXP";
	}

	function isCached($Filename, $TaskID=0) {
		$CacheFile = $this->CacheFile($Filename,$TaskID);
		return (file_exists($CacheFile) && is_file($CacheFile));
	}

	function CachedTime($Filename, $TaskID=0) {
		$CacheFile = $this->CacheFile($Filename,$TaskID);
		if(file_exists($CacheFile) && is_file($CacheFile)) return filemtime($CacheFile);
		return null;
	}

	function EmptyCache($Filename, $TaskID=0) {
		if($this->isCached($Filename,$TaskID)) return unlink($this->CacheFile($Filename,$TaskID));
		return true;
	}

	private function RebuildPreview($ArtworkID, $TaskID=0, $Page=0, $Filename, $outputpath, $MaxSize=0) {
		if (!empty($Page)) {
			$RealFilename = $Filename;
			$CacheFilename = $this->CacheFilename($Filename,$TaskID);
			$Filename = $this->CacheHandler($Filename,$TaskID);
			$name_suffix = (empty($TaskID)) ? "-$Page.jpg" : "-$Page-$TaskID.jpg";
			$name = $outputpath . BareFilename($RealFilename) . $name_suffix;
			$file = $this->RebuildXML($ArtworkID, $TaskID, $Page, $RealFilename);
			if($file === false) return false;
			$Modify = !empty($file) ? "&modify=file:$file" : "";
			$Path = $this->QuarkJPG . urlencode($Filename) . "?page=$Page&jpegquality=" . JPEG_QUALITY . "&scale=" . JPEG_SCALE . $Modify;
			$base = $this->BuildJPGBase($Path, $name, $MaxSize);
			if($base === false) return false;

			if($this->useCache && !empty($file)) {
				$url = $this->QuarkQXD . urlencode($Filename) . "?qxpdocver=" . $this->getDocVersion() . $Modify;
				file_put_contents($this->Storage.$CacheFilename, $this->curl_get_file_contents($url));
			}
			
			return basename($base);
		} else {
			$query = sprintf("SELECT pages.Page
							FROM pages
							LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
							WHERE pages.ArtworkID = %d
							ORDER BY pages.Page ASC",
							$ArtworkID);
			$result = mysql_query($query) or die(mysql_error());
			while ($row = mysql_fetch_assoc($result)) {
				$rebuild = $this->RebuildPreview($ArtworkID, $TaskID, $row['Page'], $Filename, $outputpath, $MaxSize);
				if($rebuild === false) continue;
			}
			return true;
		}
	}

	private function BuildJPGBase($Path, $NewFilePath, $MaxSize) {
		$content = $this->curl_get_file_contents($Path);
		if(empty($content) || preg_match('/^Error/i',$content)) {
			log_error("$content $Path","QXPS");
			return false;
		}
		$im = @imagecreatefromjpeg($Path);
		if ($im === false)
			return false;
		if ($MaxSize == 0) {
			imagejpeg($im, $NewFilePath);
			return $NewFilePath;
		}
		if ($im === false)
			return false;
		$height_orig = imagesy($im);
		$width_orig = imagesx($im);

		if ($height_orig > $width_orig) {
			$aspect_ratio = (float) $MaxSize / $height_orig;
		} else {
			$aspect_ratio = (float) $MaxSize / $width_orig;
		}
		$height = $height_orig * $aspect_ratio;
		$width = $width_orig * $aspect_ratio;

		$newImg = imagecreatetruecolor($width, $height);
		imagecopyresized($newImg, $im, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		imagejpeg($newImg, $NewFilePath);
		return $NewFilePath;
	}

	/* can be used for box preview
	  public function CreateBoxHashJPG($Filename, $BoxID, $output="",$MaxSize=0) {
	  $Path = $this->QuarkJPG.urlencode($Filename)."?box=$BoxID";
	  $name = "hash".time().".jpg";
	  return basename($this->CreatePagePreviwBase($Path, $output.$name, $MaxSize));
	  }
	 */

	private function RebuildDOC($ArtworkID, $TaskID=0, $Page=0, $Filename, $outputpath, $Type) {
		$row = $this->get_artwork_info($ArtworkID);
		if($row === false) return false;
		$artworkName = $row['artworkName'];

		$RealFilename = $Filename;
		$CacheFilename = $this->CacheFilename($Filename,$TaskID);
		$Filename = $this->CacheHandler($Filename,$TaskID);
		$file = $this->RebuildXML($ArtworkID, $TaskID, $Page, $RealFilename);
		if($file === false) return false;
		$code = $this->get_country_code($ArtworkID, $TaskID);
		$name = $outputpath . $artworkName . "_$code.$Type";
		switch ($Type) {
			case "PDF":
				$Path = $this->GetQuarkURL($Filename, $file);
				break;
			case "QXP":
				$Path = $this->QuarkQXD . urlencode($Filename) . "?qxpdocver=" . $this->getDocVersion() . "&modify=file:$file";
				break;
		}

		$content = $this->curl_get_file_contents($Path);
		if(empty($content) || preg_match('/^Error/i',$content)) {
			log_error("$content $Path","QXPS");
			return false;
		}
		file_put_contents($name,$content);

		if($this->useCache) {
			$url = $this->QuarkQXD . urlencode($Filename) . "?qxpdocver=" . $this->getDocVersion() . "&modify=file:$file";
			file_put_contents($this->Storage.$CacheFilename, $this->curl_get_file_contents($url));
		}

		return $name;
	}

	private function RebuildXML($ArtworkID, $TaskID=0, $Page=0, $Filename) {
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
		$XMLFile = $this->Storage . $filein . ".base";
		if(!file_exists($XMLFile) || is_dir($XMLFile)) return null;
		$XML = new QXPDOMDocument('1.0', 'UTF-8');
		$loaded = $XML->load($XMLFile);
		if($loaded === false) return false;
		$XML = $this->UpdateLayerInfo($XML,$ArtworkID);
		$XML = $this->substitute_font($XML,$ArtworkID,$TaskID);
		$XML = $this->UpdateBoxProperties($XML,$ArtworkID,$TaskID);
		$XML = $this->UpdateBoxPositions($XML,$ArtworkID,$TaskID);
		$XML = $this->ReplaceILTags($XML,$ArtworkID,$TaskID);

		//checking if the cache file is available
		$cached = $this->isCached($Filename,$TaskID);
		$box_uids = $this->GetChangedItems($ArtworkID,$TaskID);
		$xPath = new DOMXpath($XML);
		$boxes = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX");
		$tables = $xPath->query("//PROJECT/LAYOUT/SPREAD/TABLE");
		$items = array($boxes,$tables);
		foreach($items as $boxes) {
			//Loop thought boxes Removing unchanged boxes
			foreach($boxes as $box) {
				$box_uid = $box->getElementsByTagName('ID')->item(0)->getAttribute('UID');
				if( $cached && $this->useCache && !in_array($box_uid,$box_uids) && !empty($Page) ) {
					$box->parentNode->removeChild($box);
				}
			}
		}
		$contents_box = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX/TEXT/STORY/PARAGRAPH/RICHTEXT");
		$contents_table = $xPath->query("//PROJECT/LAYOUT/SPREAD/TABLE/ROW/CELL/TEXT/STORY/PARAGRAPH/RICHTEXT");
		$array = array($contents_box, $contents_table);
		foreach ($array as $contents) {
			foreach ($contents as $content) {
				$Tags = $content->nodeValue;
				//Check is PL was found
				preg_match_all('%\[PL:(\d+)\]%sim', $Tags, $PLID, PREG_PATTERN_ORDER);
				foreach ($PLID[0] as $K => $Tag) {
					$PL = $PLID[1][$K];
					$SourcePara = $this->GetParaByPL($PL);
					if ($SourcePara === false)
						continue;
					$SourceParaText = $SourcePara['ParaText'];
					$SourceParaGroup = $SourcePara['ParaGroup'];

					// check if ignored
					$ignored = $this->FinalCheckParaIgnore($PL,$TaskID);
					if ($ignored !== false) $changed = true;
					if ($ignored) {
						$Para = "";
					} else {
						if (empty($TaskID)) {
							$Para = $SourceParaText;
						} else {

								//check if user has picked any translation
								$TransPara = $this->GetTransPara($TaskID, $PL);
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
					$Tags = str_ireplace($Tag,$Para,$Tags);
				}
				$Tags = $this->PostParsaPara($Tags);
				$content->nodeValue = $Tags;
			}
		}
		$PageRef = $this->GetPageRef($ArtworkID,$Page);
		if(!empty($Page)) $XML = $this->XMLCleanUp($XML,$PageRef);
		$outFile = "{$filein}-{$TaskID}.xml";
		$XML->save($this->Storage.$outFile);
		if($this->useCache) $this->ClearItemChanges($ArtworkID,$TaskID,$PageRef);
		$this->CheckProgress($TaskID);
		return $outFile;
	}

	private function XMLCleanUp($XML, $PageRef) {
		$xpath = new DOMXPath($XML);
		$page_ids = $xpath->query(sprintf('//PROJECT/LAYOUT/SPREAD/PAGE/ID[@UID=%d]',$PageRef));
		if($page_ids->length == 0) return false;
		$page_id = $page_ids->item(0);
		$spread = $page_id->parentNode->parentNode;
		$keep_spread_uid = $spread->getElementsByTagName('ID')->item(0)->getAttribute('UID');
		$spreads = $xpath->query('//PROJECT/LAYOUT/SPREAD');
		foreach($spreads as $spread) {
			$spread_uid = $spread->getElementsByTagName('ID')->item(0)->getAttribute('UID');
			if($spread_uid == $keep_spread_uid) continue;
			$spread->parentNode->removeChild($spread);
		}
		return $XML;
	}

	public function CheckOverflow($ArtworkID, $TaskID=0) {
		//Reset overflowed boxes
		$update = sprintf("DELETE FROM box_overflows
						WHERE artwork_id = %d
						AND task_id = %d
						AND overflow = 1",
						$ArtworkID,
						$TaskID);
		$result = mysql_query($update, $this->link) or die(mysql_error());

		$query = sprintf("SELECT fileName
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$ArtworkID);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		if(!mysql_num_rows($result)) return false;
		$row = mysql_fetch_assoc($result);

		$QXPFile = $row['fileName'];
		$XMLFile = BareFilename($QXPFile) . "-$TaskID.xml";
		$URL = $this->GetQuarkXML() . $QXPFile . "?modify=file:" . urlencode($XMLFile);
		$XMLData = @$this->curl_get_file_contents($URL);
		$XML = new DOMDocument('1.0', 'UTF-8');
		$XML->loadXML($XMLData);
		$xPath = new DOMXpath($XML);
		$overmatters_box = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX/TEXT/STORY/OVERMATTER");
		$overmatters_cell = $xPath->query("//PROJECT/LAYOUT/SPREAD/TABLE/ROW/CELL/TEXT/STORY/OVERMATTER");
		$array = array("BOX" => $overmatters_box, "CELL" => $overmatters_cell);
		foreach ($array as $K => $overmatters) {
			foreach ($overmatters as $overmatter) {
				switch ($K) {
					case "BOX":
						$BoxID = $overmatter->parentNode->parentNode->parentNode->getElementsByTagName('ID')->item(0)->getAttribute('UID');
						break;
					case "CELL":
						$BoxID = $overmatter->parentNode->parentNode->parentNode->parentNode->parentNode->getElementsByTagName('ID')->item(0)->getAttribute('UID');
						break;
				}
				$query = sprintf("SELECT boxes.uID
								FROM boxes
								LEFT JOIN pages ON boxes.PageID = pages.uID
								WHERE boxes.BoxUID = %d
								AND pages.ArtworkID = %d",
								$BoxID,
								$ArtworkID);
				$result = mysql_query($query, $this->link) or die(mysql_error());
				$found = mysql_num_rows($result);
				if ($found) {
					$row = mysql_fetch_assoc($result);
					$prep_str .= "($ArtworkID,{$row['uID']},$TaskID,1),";
				}
			}
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

	function UpdateBoxProperties($XML, $artworkID, $taskID=0) {
		$query = sprintf("SELECT box_properties.lock, box_properties.resize,
						boxes.BoxUID, boxes.Type
						FROM box_properties
						LEFT JOIN boxes ON boxes.uID = box_properties.box_id
						WHERE box_properties.artwork_id = %d
						AND box_properties.task_id IN (0,%d)
						ORDER BY box_properties.box_id ASC, box_properties.task_id ASC",
						$artworkID,
						$taskID);
		$result = mysql_query($query, $this->link) or die(mysql_error());

		$xPath = new DOMXpath($XML);
		while ($row = mysql_fetch_assoc($result)) {
			$boxes = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX/ID[@UID={$row['BoxUID']}]");
			$cells = $xPath->query("//PROJECT/LAYOUT/SPREAD/TABLE/ID[@UID={$row['BoxUID']}]");
			$array = array($boxes, $cells);
			foreach ($array as $items) {
				if ($items->length == 0)
					continue;
				$item = $items->item(0);
				$box = $item->parentNode;
				if ($row['resize']) {
					switch ($row['Type']) {
						case "TEXT":
							$box->getElementsByTagName('TEXT')->item(0)->getElementsByTagName('STORY')->item(0)->setAttribute('FITTEXTTOBOX', 'true');
							break;
						case "PICT":
							$box->getElementsByTagName('PICTURE')->item(0)->setAttribute('FIT', 'FITPICTURETOBOX');
							break;
					}
				}
				if ($row['lock'])
					$this->remove_box($XML, $box);
			}
		}
		return $XML;
	}

	function UpdateBoxPositions($XML, $artworkID, $taskID=0) {
		$query = sprintf("SELECT box_moves.left, box_moves.right, box_moves.top, box_moves.bottom, box_moves.angle,
						boxes.BoxUID
						FROM box_moves
						LEFT JOIN boxes ON box_moves.box_id = boxes.uID
						WHERE box_moves.artwork_id = %d
						AND box_moves.task_id IN (0,%d)
						ORDER BY box_moves.box_id ASC, box_moves.task_id ASC",
						$artworkID,
						$taskID);
		$result = mysql_query($query, $this->link) or die(mysql_error());
		$xPath = new DOMXpath($XML);
		while ($row = mysql_fetch_assoc($result)) {
			$boxes = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX/ID[@UID={$row['BoxUID']}]");
			$cells = $xPath->query("//PROJECT/LAYOUT/SPREAD/TABLE/ID[@UID={$row['BoxUID']}]");
			$array = array($boxes, $cells);
			foreach ($array as $items) {
				if ($items->length == 0)
					continue;
				$item = $items->item(0);
				$box = $item->parentNode;
				$geo = $box->getElementsByTagName("GEOMETRY")->item(0);
				$geo->setAttribute("ANGLE", $row['angle']);
				$pos = $geo->getElementsByTagName("POSITION")->item(0);
				$pos->getElementsByTagName("TOP")->item(0)->nodeValue = $row['top'];
				$pos->getElementsByTagName("BOTTOM")->item(0)->nodeValue = $row['bottom'];
				$pos->getElementsByTagName("LEFT")->item(0)->nodeValue = $row['left'];
				$pos->getElementsByTagName("RIGHT")->item(0)->nodeValue = $row['right'];
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
			$layer_ids = $xpath->query(sprintf("//PROJECT/LAYOUT/LAYER/ID[@UID=%d]", $layer_id));
			if ($layer_ids->length == 0)
				continue;
			$layer_id = $layer_ids->item(0);
			$layer = $layer_id->parentNode;
			$layer->setAttribute('VISIBLE', $layer_visible);
			$layer->setAttribute('LOCKED', $layer_locked);
			//QXPS doesn't seem like default layer colour update but might support it in future
			$RGBCOLOR = $layer->getElementsByTagName('RGBCOLOR')->item(0);
			$RGBCOLOR->setAttribute('RED', hexdec(substr($layer_colour, 0, 2)));
			$RGBCOLOR->setAttribute('GREEN', hexdec(substr($layer_colour, 2, 2)));
			$RGBCOLOR->setAttribute('BLUE', hexdec(substr($layer_colour, 4, 2)));
			if (!$row['visible'] || $row['locked']) {
				$BOX_GEOMETRYs = $xpath->query(sprintf("//PROJECT/LAYOUT/SPREAD/BOX/GEOMETRY[@LAYER='%s']", $layer_name));
				$TABLE_GEOMETRYs = $xpath->query(sprintf("//PROJECT/LAYOUT/SPREAD/TABLE/GEOMETRY[@LAYER='%s']", $layer_name));
				if ($BOX_GEOMETRYs->length == 0 && $TABLE_GEOMETRYs->length == 0)
					continue;
				$array = array($BOX_GEOMETRYs, $TABLE_GEOMETRYs);
				foreach ($array as $GEOMETRYs) {
					foreach ($GEOMETRYs as $GEOMETRY) {
						$box = $GEOMETRY->parentNode;
						$this->remove_box($XML, $box);
					}
				}
			}
		}
		return $XML;
	}

	protected function remove_box(DOMDocument $XML, DOMElement $box) {
		if (!$XML instanceof DOMDocument || !$box instanceof DOMElement)
			return false;
		$box->parentNode->removeChild($box);
	}

	function ReplaceILTags($XML, $artworkID, $taskID=0) {
		$xPath = new DOMXpath($XML);
		$contents = $xPath->query("//*/CONTENT");
		foreach ($contents as $content) {
			$tag = $content->nodeValue;
			preg_match('%\[IL:(\d+)\]%i', $tag, $match);
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
							$artworkID,
							$taskID);
			$result = mysql_query($query, $this->link) or die(mysql_error());
			if (mysql_num_rows($result)) {
				$row = mysql_fetch_assoc($result);
				$img = $row['content'];
			} else {
				//quark will report error if we use original image path
				$img = "";
			}
			//replace tags
			$content->nodeValue = $img;
		}
		return $XML;
	}

	function substitute_font($xml, $artwork_id, $task_id=0) {
		$xPath = new DOMXpath($xml);
		$richtexts = $xPath->query("//*/RICHTEXT");
		foreach ($richtexts as $richtext) {
			$font_family = $richtext->getAttribute('FONT');
			$font_name = $richtext->getAttribute('PSFONTNAME');
			$font_id = $this->get_font_id($font_family, $font_name, "", ENGINE_QUARK_ID);
			$sub_font_id = $this->get_sub_font_id($artwork_id, $font_id, $task_id);
			$sub_font_info = $this->get_font_info($sub_font_id);
			$richtext->setAttribute('FONT', $sub_font_info['family']);
			$richtext->setAttribute('PSFONTNAME', $sub_font_info['name']);
		}
		return $xml;
	}

	function convert_box_type($box_type) {
		switch (strtoupper($box_type)) {
			case "CT_PICT":
				$box_type = "PICT";
				break;
			case "CT_TEXT":
				$box_type = "TEXT";
				break;
			default:
				$box_type = "NONE";
				break;
		}
		return $box_type;
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

	/**
	 * Cleans Para before inserting into database
	 *
	 * @param string $Para
	 * @return string
	 */
	public function ParsaPara($Para) {
		$Keys = array();
		$Para = str_ireplace(array_keys($Keys), array_values($Keys), $Para);
		return parent::ParsaPara($Para);
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

	/*	 * ******************************************************************
	  start of template based functions
	 * ****************************************************************** */

	public function RebuildFileTemp($ArtworkID, $RecordID, $Page=0, $outputpath, $Type="JPG", $MaxSize=0) {
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

	public function RebuildPreviewTemp($ArtworkID, $RecordID, $Page=0, $Filename, $outputpath, $MaxSize=0) {
		if (!empty($Page)) {
			$name = $outputpath . BareFilename($Filename) . "-$Page.jpg";
			$file = $this->RebuildXMLTemp($ArtworkID, $RecordID, $Page, $Filename);
			if ($file === false)
				return false;
			$Path = $this->QuarkJPG . urlencode($Filename) . "?page=$Page&jpegquality=" . JPEG_QUALITY . "&scale=" . JPEG_SCALE . "&modify=file:$file";
			$base = $this->BuildJPGBase($Path, $name, $MaxSize);
			if($base === false) return false;
			return basename($base);
		} else {
			$query = sprintf("SELECT pages.Page
							FROM pages
							LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
							WHERE pages.ArtworkID = %d
							ORDER BY pages.Page ASC",
							$ArtworkID);
			$result = mysql_query($query) or die(mysql_error());
			while ($row = mysql_fetch_assoc($result)) {
				$rebuild = $this->RebuildPreviewTemp($ArtworkID, $RecordID, $row['Page'], $Filename, $outputpath, $MaxSize);
				if ($rebuild === false)
					continue;
			}
			return true;
		}
	}

	private function RebuildDOCTemp($ArtworkID, $RecordID, $Page=0, $Filename, $outputpath, $Type) {
		$file = $this->RebuildXMLTemp($ArtworkID, $RecordID, $Page, $Filename);
		if ($file === false)
			return false;
		switch ($Type) {
			case "PDF":
				//$Path = $this->QuarkLowPDF.urlencode($Filename).$this->GetQuarkLowPDFSettings()."&modify=file:$file";
				//$Path = $this->QuarkHighPDF.urlencode($Filename).$this->GetQuarkHighPDFSettings()."&modify=file:$file";
				$Path = $this->GetQuarkURL($Filename, $file);
				break;
			case "QXP":
				$Path = $this->QuarkQXD . urlencode($Filename) . "?qxpdocver=" . $this->getDocVersion() . "&modify=file:$file";
				break;
		}
		$name = $outputpath . BareFilename($Filename) . "-$RecordID.$Type";

		$content = $this->curl_get_file_contents($Path);
		if(empty($content) || preg_match('/^Error/i',$content)) {
			log_error("$content $Path","QXPS");
			return false;
		}

		file_put_contents($name,$content);
		return $name;
	}

	private function RebuildXMLTemp($ArtworkID, $RecordID, $Page=0, $Filename) {
		if (empty($RecordID))
			return false;
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

		$filein = BareFilename($Filename);
		$XMLFile = $this->Storage . $filein . ".base";
		if (!file_exists($XMLFile) || is_dir($XMLFile))
			return false;
		$XML = new QXPDOMDocument('1.0', 'UTF-8');
		$loaded = $XML->load($XMLFile);
		if ($loaded === false)
			return false;
		$XML = $this->UpdateLayerInfo($XML, $ArtworkID);
		$XML = $this->substitute_font($XML, $ArtworkID, 0);
		$XML = $this->UpdateBoxProperties($XML, $ArtworkID, 0);
		$XML = $this->UpdateBoxPositions($XML, $ArtworkID, 0);
		$XML = $this->ReplaceILTags($XML, $ArtworkID, 0);

		$xPath = new DOMXpath($XML);
		$contents_box = $xPath->query("//PROJECT/LAYOUT/SPREAD/BOX/TEXT/STORY/PARAGRAPH/RICHTEXT");
		$contents_table = $xPath->query("//PROJECT/LAYOUT/SPREAD/TABLE/ROW/CELL/TEXT/STORY/PARAGRAPH/RICHTEXT");
		$array = array($contents_box, $contents_table);
		foreach ($array as $contents) {
			foreach ($contents as $content) {
				$Tags = $content->nodeValue;
				//Check is PL was found
				preg_match_all('%\[PL:(\d+)\]%sim', $Tags, $PLID, PREG_PATTERN_ORDER);
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
					$Tags = str_ireplace($Tag, $Para, $Tags);
				}
				$Tags = $this->PostParsaPara($Tags);
				$content->nodeValue = $Tags;
			}
		}
		$PageRef = $this->GetPageRef($ArtworkID,$Page);
		if(!empty($Page)) $XML = $this->XMLCleanUp($XML,$PageRef);
		$outFile = "$filein.xml";
		$XML->save($this->Storage.$outFile);
		return $outFile;
	}

	/*	 * ******************************************************************
	  end of template based functions
	 * ****************************************************************** */
}