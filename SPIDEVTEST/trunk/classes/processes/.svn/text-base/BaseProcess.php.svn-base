<?php
//BaseProcess.php
interface ProcessFrame {
	function import($ArtworkID, $TaskID, $file, $option=1, $CS=true);
	function export($ArtworkID, $TaskID, $lines);
	function UploadFile($ArtworkID, $filein);
	function RebuildBase($ArtworkID, $filein);
	function TweakFile($ArtworkID, $TaskID, $filein);
	function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true);
	function GetPDFDefault();
	function GetPDFOptions();
}

require_once(CLASSES.'translator.php');

abstract class Process extends Translator implements ProcessFrame {
	protected $config = array();
	function import($ArtworkID, $TaskID, $file, $option=1, $CS=true) {
		return false;
	}
	
	function export($ArtworkID, $TaskID, $lines=0) {
		return false;
	}
	
	function UploadFile($ArtworkID, $filein) {
		return false;
	}
	
	function RebuildBase($ArtworkID, $filein) {
		return false;
	}

	function TweakFile($ArtworkID, $TaskID, $filein) {
		return false;
	}
	
	function DownloadFile($ArtworkID, $TaskID=0, $record_id=0, $packed=true) {
		return false;
	}

	function GetPDFOptions(){
		return array();
	}
	function GetPDFDefault(){
		return null;
	}
	
	private $opt;
	function setPDFOption($opt){
		$opt = (!is_null($opt))?$opt:$this->GetPDFDefault();
		$pdfOpts = $this->GetPDFOptions();
		if(!isset($pdfOpts[$opt])) trigger_error ('Invalid PDF Option', E_USER_ERROR);
		$this->opt = $opt;
	}
	function getPDFOption(){
		return $this->opt;
	}

	//Config Setup
	function setConfigs(array $config){
		$this->config = $config;
	}
	function getConfigs(){
		return $this->config;
	}
	function resetConfigs(){
		$this->config = array();
	}
	function getConfig($key){
		return (isset($this->config[$key])) ? $this->config[$key] : null;
	}
	function addConfig($key, $value){
		$this->config[$key] = $value;
	}
	function removeConfig($key){
		unset($this->config[$key]);
	}
	function updateConfig($key, $value){
		$this->addConfig($key, $value);
	}
}
