<?php
/**
 *Services
 */
class ProcessService extends ServicesDatabase {
	private $ProcessEngine;
	function __construct($service_tID) {
		if(empty($service_tID)) trigger_error("EngineService Invalid Artwork ID", E_USER_ERROR);
		parent::__construct();
		$process = $this->getDBProcessEngine($service_tID);
		if(!file_exists(PROCESSES.$process["File"]) || !is_file(PROCESSES.$process["File"])) {
			trigger_error("Engine '{$process["File"]}' File is Missing", E_USER_ERROR);
		}
		require_once(PROCESSES.$process["File"]);
		if(!class_exists($process["Class"])) {
			trigger_error("Engine '{$process["Class"]}' Class is Missing", E_USER_ERROR);
		}
		$this->ProcessEngine = new $process["Class"]();
	}
	function getProcessEngine() {
		return $this->ProcessEngine;
	}
}

class EngineService extends ServicesDatabase{
	private $artworkID;
	private $Engine;
	
	function __construct($artworkID,$isService=false) {
		if(empty($artworkID)) trigger_error("EngineService Invalid Artwork ID", E_USER_ERROR);
		parent::__construct();
		$this->artworkID = $artworkID;
		$process = $isService ? $this->getDBEngineByService($artworkID) : $this->getDBEngine($artworkID);
		if(!file_exists(ENGINES.$process["File"]) || !is_file(ENGINES.$process["File"])) trigger_error("Engine '{$process["File"]}' File is Missing", E_USER_ERROR);
		require_once(ENGINES.$process["File"]);
		if(!class_exists($process["Class"])) trigger_error("Engine '{$process["Class"]}' Class is Missing", E_USER_ERROR);
		$this->Engine = new $process["Class"]();
	}

	function isCached($Filename, $TaskID) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->isCached($Filename,$TaskID);
	}

	function CachedTime($Filename, $TaskID=0) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->CachedTime($Filename,$TaskID);
	}

	function EmptyCache($Filename, $TaskID=0) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->EmptyCache($Filename,$TaskID);
	}
	
	function IsServerRunning($timeout=10) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->IsServerRunning($timeout);
	}
	
	function GetStorage() {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->GetStorage();
	}
	
	function isValidFile($FileName, $Path ="", $FileTypes=null) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->isValidFile($FileName, $Path, $FileTypes);
	}
	
	function getDocInfo() {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->getDocInfo();
	}
	
	function SetPreviewOutputPath($path) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->SetPreviewOutputPath($path);
	}
	
	function RebuildFile($ArtworkID, $TaskID=0, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->RebuildFile($ArtworkID, $TaskID, $Page, $outputpath, $Type, $MaxSize);
	}
	
	function RebuildFileTemp($ArtworkID, $RecordID, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->RebuildFileTemp($ArtworkID, $RecordID, $Page, $outputpath, $Type, $MaxSize);
	}
	
	function CheckOverflow($ArtworkID, $TaskID=0) {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->CheckOverflow($ArtworkID, $TaskID);
	}
	
	function GetInstalledFonts() {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->GetInstalledFonts();
	}
	
	function GetFileFonts() {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->GetFileFonts();
	}
	
	function GetServerVersion() {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->GetServerVersion();
	}
	
	function ServerInfo() {
		if(!method_exists($this->Engine,__FUNCTION__)) trigger_error(__FUNCTION__." Function is Missing", E_USER_ERROR);
		return $this->Engine->ServerInfo();
	}
	
}

class ServicesDatabase {
	
	private $conn;
	
	function __construct() {
		require_once(dirname(__FILE__)."/../config.php");
		$this->conn = mysql_connect(HOST_NAME, DB_USER_NAME, DB_PASSWORD) or die(mysql_error());
		mysql_select_db(DB_NAME, $this->conn);
		mysql_query("SET CHARACTER SET 'utf8'", $this->conn);
		mysql_query("SET NAMES 'utf8'", $this->conn);
	}
	
	function getDBEngine($artworkID) {
		$SQL = sprintf("SELECT engineFile as File, engineClass as Class
						FROM service_engines
						LEFT JOIN artworks ON artworks.artworkType = service_engines.id
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$artworkID);
		$result = mysql_query($SQL,$this->conn) or die($SQL.mysql_error());
		$row = mysql_fetch_assoc($result);
		return $row;
	}
	
	function getDBEngineByService($serviceID) {
		$SQL = sprintf("SELECT engineFile as File, engineClass as Class
						FROM service_engines
						WHERE service_engines.id = %d
						LIMIT 1",
						$serviceID);
		$result = mysql_query($SQL,$this->conn) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		return $row;
	}

	function getDBProcessEngine($service_tID) {
		$SQL = sprintf("SELECT file as File, class as Class
						FROM service_transaction_process
						WHERE id = %d LIMIT 1",
						$service_tID);
		$result = mysql_query($SQL, $this->conn) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		return $row;
	}
}
?>