<?php
require_once(CONFIGS . 'config.soap.php');
class InDesignSoap {
	private $SoapClient;
	private $ServerURL = "";
	private $ServerPort = 0;
	private $LastTimeout = null;

	private $ServerModule = INDS_SERVER_MODULES;
	private $ServerScript = INDS_SERVER_SCRIPT;

	function  __construct($ServerScript="") {
		if(!empty($ServerScript)) $this->setServerScript($ServerScript);
	}

    public function getServers(){
    	return $this->Servers;
    }
    public function getServerID(){
    	return $this->ServerID;
    }
    public function setServerID($ServerID){
    	$this->ServerID = $ServerID;
    }
    public function getServerModule(){
    	return $this->ServerModule;
    }

	public function setServerURL($ServerURL){
		$this->ServerURL = $ServerURL;
	}

	public function getServerURL(){
		return $this->ServerURL;
	}

	public function setServerPort($ServerPort){
		$this->ServerPort = $ServerPort;
	}
	public function getServerPort(){
		return $this->ServerPort;
	}

	function setServerModule($ServerModule){
		$this->ServerModule = $ServerModule;
	}

	function setServerScript($ServerScript){
		$this->ServerScript = $ServerScript;
	}
	function getServerScript(){
		return $this->ServerScript;
	}

	function SendRequest($request,$timeout=120){
		#if($this->LastTimeout != $timeout || !$this->SoapClient){
			$this->LastTimeout = $timeout;
			try {
				use_soap_error_handler(false);
				ini_set('default_socket_timeout', $timeout);
				$soapConfig = array(
					'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
					#"soap_version" => SOAP_1_2,
					"connection_timeout" => $timeout
				);
				$this->SoapClient = new SoapClient(sprintf("%s:%d/service?wsdl",$this->getServerURL(),$this->getServerPort(),$soapConfig));
                                $this->SoapClient->__setLocation(sprintf("%s:%d",$this->getServerURL(),$this->getServerPort()));
			} catch (Exception $e) {
				log_error($e->getMessage(),"SoapClient");
				return false;
			}
		#}

		$scriptArgs = $request->getArray();

		$scriptFile = sprintf("%s\%s",$this->getServerModule(),$this->getServerScript());
		if(!file_exists($scriptFile)){
			trigger_error("Script '$scriptFile' Not found\n", E_USER_ERROR);
			return false;
		}
		$script_parameters = array(
			"scriptFile" => $scriptFile,
			"scriptArgs" => $scriptArgs,
			"scriptLanguage" => 'javascript'
		);
		$script_data = array("runScriptParameters" => $script_parameters);
		try{
			$return_value = $this->SoapClient->RunScript($script_data);
		}catch(Exception $e){
			log_error($e->getMessage(),"RunScript");
			return false;
		}
		if($return_value->errorNumber) return $return_value->errorString;
		return true;
	}
}

class InDesignSoapRequest {
	private $Command = "XML";
	private $InDesignFile = "";
	private $OutputPath = "";

	private $Type = "JPG";
	private $Pages = 0;
	private $XML = "XML/BASE.XML";

	private $DocName = "";
	private $OutFolder = "";
	private $OutSave = null;

	private $out_dpi = 144;
	private $out_quality = 2;
	private $pdf_profile = '';

	function  __construct($InDesignFile="") {
		if(!empty($InDesignFile)) $this->setInDesignFile($InDesignFile);
	}

	public function setCommand($Command){
		$this->Command = $Command;
	}
	public function getCommand(){
		return $this->Command;
	}

	public function setPDFProfile($pdf_profile){
		$this->pdf_profile = $pdf_profile;
	}
	public function getPDFProfile(){
		return $this->pdf_profile;
	}

	public function setInDesignFile($InDesignFile){
		if(!file_exists($InDesignFile)){
			trigger_error("InDesignFile '$InDesignFile' doesn't exist\n", E_USER_ERROR);
			return false;
		}
		$this->InDesignFile = $InDesignFile;
	}
	public function getInDesignFile(){
		return $this->InDesignFile;
	}

	public function setOutputPath($OutputPath){
		if(!is_dir($OutputPath)){
			trigger_error("Invalid OutputPath '$OutputPath'\n", E_USER_ERROR);
			return false;
		}
		$this->OutputPath = $OutputPath;
	}
	public function getOutputPath(){
		return $this->OutputPath;
	}

	public function setType($Type){
		$this->Type = $Type;
	}
	public function getType(){
		return $this->Type;
	}

	public function setDocName($DocName){
		$this->DocName = $DocName;
	}
	public function getDocName(){
		return $this->DocName;
	}

	public function setOutSave($OutSave){
		$this->OutSave = $OutSave;
	}
	public function getOutSave(){
		return $this->OutSave;
	}
	public function setOutFolder($OutFolder){
		$this->OutFolder = $OutFolder;
	}
	public function getOutFolder(){
		return $this->OutFolder;
	}

	public function setPages($Pages){
		$this->Pages = $Pages;
	}
	public function getPages(){
		return $this->Pages;
	}

	public function setXML($XML){
		$this->XML = $XML;
	}
	public function getXML(){
		return $this->XML;
	}

	public function getOutDpi(){
		return $this->out_dpi;
	}

	public function setOutDpi($dpi){
		$this->out_dpi = $dpi;
	}

	public function getOutQuality(){
		return $this->out_quality;
	}

	public function setOutQuality($quality){
		$this->out_quality = $quality;
	}

	function getArray(){
		$resultArray = array(
		array("name" => "Command","value" => $this->getCommand()),
		array("name" => "InDesignFile","value" => $this->getInDesignFile()),
		array("name" => "OutputPath","value" => $this->getOutputPath()),
		array("name" => "Type","value" => $this->getType()),
		array("name" => "XML","value" => $this->getXML()),
		array("name" => "OUT_DPI","value" => $this->getOutDpi()),
		array("name" => "OUT_Quality","value" => $this->getOutQuality()),
		array("name" => "PDF_Profile","value" => $this->getPDFProfile()),
		array("name" => "OutFolder","value" => $this->getOutFolder()),
		array("name" => "DocName","value" => $this->getDocName()),
		array("name" => "OUT_SAVE","value" => $this->getOutSave()),
		);

		if($this->getPages()) $resultArray[] = array("name" => "Pages","value" => $this->getPages());
		return $resultArray;
	}
}

class InDesignSoapRunningRequest {
	function getArray(){
		$resultArray = array();
		return $resultArray;
	}
}

class InDesignServerRequest{
	private $ServerID = false;
	private $ServerModule ="";
	private $Servers = array(
                #"INDS_SESSION_DEFAULT_5001"=>array("URL"=>"http://192.168.1.129", "PORT"=>"5001"),
                "INDS_SESSION_DEFAULT_5002"=>array("URL"=>"http://localhost", "PORT"=>"5002"),
                "INDS_SESSION_DEFAULT_5003"=>array("URL"=>"http://localhost", "PORT"=>"5003"),
                "INDS_SESSION_DEFAULT_5004"=>array("URL"=>"http://localhost", "PORT"=>"5004"),
            );

	protected $InDesignSoap;
	protected $running = false;
	protected $pdfType = 4;

	public function setPDFType($pdfType){
		$this->pdfType = $pdfType;
	}
	public function getPDFType(){
		return $this->pdfType;
	}

	function __construct() {
	  ignore_user_abort(true);
	  set_time_limit(0);

	  $this->InDesignSoap = new InDesignSoap();
	  $this->InDesignSoap->setServerScript(INDS_SERVER_CHECKER);
	  $this->ServerModule = $this->InDesignSoap->getServerModule();
	  $request = new InDesignSoapRunningRequest();

	  foreach($this->Servers as $ServerID => $ServerDetails) {
	    if(file_exists($this->ServerModule."/".$ServerID)) {
	      $use = file_get_contents($this->ServerModule."/".$ServerID);
	    }else {
	      $use = "0";
	    }

	    if($use == "0") {
	      $this->ServerID = $ServerID;

	      $this->InDesignSoap->setServerURL($this->Servers[$this->ServerID]['URL']);
	      $this->InDesignSoap->setServerPort($this->Servers[$this->ServerID]['PORT']);
	      $this->running = $this->InDesignSoap->SendRequest($request,5);
	      if($this->running !== false) break;
	    }
	  }
	  if($this->ServerID) file_put_contents($this->ServerModule."/".$this->ServerID,"1");
	}

	function  __destruct() {
	  $this->CleanUp();
	}

	function CleanUp() {
		if ($this->ServerID) {
			file_put_contents($this->ServerModule . "/" . $this->ServerID, "0");
			unlink($this->ServerModule . "/" . $this->ServerID);
		}
	}

	function isRunning() {
	    return $this->running;
	}

	function ServerInfo(){
		$this->InDesignSoap->setServerScript(INDS_SERVER_SCRIPT);
		$request = new InDesignSoapRequest();
		$request->setCommand("SERVERINFO");
		//$request->setType($type);
		//$request->setPages(1);
		$request->setXML(INDS_FONTS_LOG);
		return $this->InDesignSoap->SendRequest($request,3600);
	}

	function IDSUpload($filename,$type){
		$_SESSION['joblog'] = dirname($filename)."/Output/".basename($filename)."/Progress.log";
		$type = explode('.',$type);
		$type = array_unique($type);
		$type = '.'.implode('.', $type);
		
		#$this->ServerInfo($filename);
		$this->InDesignSoap->setServerScript(INDS_SERVER_SCRIPT);
		$request = new InDesignSoapRequest($filename);
		$request->setCommand("XML");
		$request->setOutputPath(OUTPUT_DIR);
		$request->setType($type);
		$request->setOutDpi(144);
		$request->setOutQuality(JPEG_QUALITY);
		//$request->setPages(1);
		$request->setXML("XML/BASE.XML");
		/*
			0 = [High Quality Print]
			1 = [PDF/X-1a:2001]
			2 = [PDF/X-3:2002]
			3 = [PDF/X-4:2008]
			4 = [Press Quality]
			5 = [Smallest File Size]
		*/
		$request->setPDFProfile(5);
		return $this->InDesignSoap->SendRequest($request,3600);
	}

	function IDSTranslate($filename,$outfolder,$type,$pages,$xml,$DocName="",$cachefile=null){
		$_SESSION['joblog'] = dirname($filename)."/../Progress.log";
		$type = explode('.',$type);
		$type = array_unique($type);
		$type = '.'.implode('.', $type);
		$this->InDesignSoap->setServerScript(INDS_SERVER_SCRIPT);
		$request = new InDesignSoapRequest($filename);
		$request->setCommand("Update");
		$request->setOutputPath(OUTPUT_DIR);
		$request->setType($type.".XML");
		$request->setOutDpi(144);
		$request->setOutQuality(JPEG_QUALITY);
		$request->setPages($pages);
		$request->setXML($xml);
		if(!is_null($cachefile)) $request->setOutSave($cachefile);
		$request->setOutFolder($outfolder);
		$request->setDocName($DocName);
		$request->setPDFProfile($this->getPDFType()); // '' = custom includes crop marks and colour's
		return $this->InDesignSoap->SendRequest($request,3600);
	}

}