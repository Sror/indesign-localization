<?php
//BaseService.php
interface ServicesFrame{
	//must have. functions called from other scripts
	function isCached($Filename, $TaskID=0);
	function CachedTime($Filename, $TaskID=0);
	function EmptyCache($Filename, $TaskID=0);
	function IsServerRunning($timeout=10);
	function GetStorage();
	function isValidFile($FileName, $FilePath ="");
	function getDocInfo();
	function SetPreviewOutputPath($path);
	function RebuildFile($ArtworkID, $TaskID=0, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0);
	function RebuildFileTemp($ArtworkID, $RecordID, $Page=0, $outputpath="", $Type="JPG", $MaxSize=0);
	function CheckOverflow($ArtworkID, $TaskID=0);
	function SetPreviewOutputPath($PreviewOutputPath);
	function GetInstalledFonts();
	function GetFileFonts();
	function GetServerVersion();
	function ServerInfo();
}


abstract class Service implements ServicesFrame{
	//default settings for service engines
	function __construct() {
		//echo "Base";
	}

	function isCached($Filename, $TaskID=0) {
		return false;
	}

	function CachedTime($Filename, $TaskID=0) {
		return false;
	}

	function EmptyCache($Filename, $TaskID=0) {
		return false;
	}

	function IsServerRunning($timeout=10) {
		return false;
	}
	
	function ServerInfo(){
		return false;
	}
}
?>